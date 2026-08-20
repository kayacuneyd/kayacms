<?php

namespace App\Controllers;

use App\Models\ContentBookmarkModel;
use Content\Models\ContentModel;
use Setting\Models\SettingModel;
use Media\Libraries\ImageProcessor;
use User\Libraries\MagicLink;
use User\Libraries\Mailer;
use User\Models\UserModel;

class MemberAuthController extends BaseController
{
    public function form(): string
    {
        return view('member/login', [
            'title'  => 'Member sign-in',
            'member' => $this->memberSession(),
        ]);
    }

    public function requestLink()
    {
        if (trim((string) $this->request->getPost('website')) !== '') {
            return redirect()->back()->with('success', 'A sign-in link has been sent to your email.');
        }

        $lastRequest = (int) (session('member_magic_link_at') ?? 0);
        if ($lastRequest > 0 && time() - $lastRequest < 60) {
            return redirect()->back()->with('error', 'Please wait a moment before requesting another link.');
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Enter a valid email address.');
        }

        $settings = new SettingModel();
        if (! (bool) $settings->getSetting('magic_link_enabled', true)) {
            return redirect()->back()->with('error', 'Passwordless sign-in is currently disabled.');
        }

        $users = new UserModel();
        $user = $users->withDeleted()->where('email', $email)->first();

        if (! $user) {
            if (! (bool) $settings->getSetting('enable_registration', true)) {
                return redirect()->back()->with('success', 'If that email is registered, a sign-in link has been sent.');
            }

            $base = preg_replace('/[^a-z0-9._-]+/i', '-', strstr($email, '@', true) ?: 'member');
            $username = strtolower(trim((string) $base, '-')) ?: 'member';
            $candidate = $username;
            $suffix = 2;

            while ($users->withDeleted()->where('username', $candidate)->first()) {
                $candidate = $username . '-' . $suffix++;
            }

            $id = $users->insert([
                'username'      => $candidate,
                'email'         => $email,
                'password_hash' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                'role_id'       => null,
                'status'        => 'active',
            ]);
            $user = $users->find((int) $id);
        }

        if (! $user || ($user->status ?? 'inactive') !== 'active') {
            return redirect()->back()->with('success', 'If that email is registered, a sign-in link has been sent.');
        }

        session()->set('member_magic_link_at', time());

        $token = (new MagicLink())->issue((int) $user->id);
        $link = site_url('member/login/' . $token);
        $sent = (new Mailer())->sendView($email, 'Your member sign-in link', 'emails/member_magic_link', [
            'link'  => $link,
            'email' => $email,
        ]);

        if ($sent !== true) {
            return redirect()->back()->with('error', 'The sign-in link could not be sent. Check the mail settings.');
        }

        return redirect()->back()->with('success', 'A sign-in link has been sent to your email.');
    }

    public function consume(string $token)
    {
        $user = (new MagicLink())->consume($token);
        if (! $user || ($user->status ?? 'inactive') !== 'active') {
            return redirect()->to(site_url('member'))->with('error', 'This sign-in link is invalid or has expired.');
        }

        session()->set([
            'member_id'    => (int) $user->id,
            'member_email' => (string) $user->email,
            'member_name'  => (string) ($user->username ?: $user->email),
            'member_avatar' => (string) ($user->avatar_path ?? ''),
        ]);

        return redirect()->to(site_url('/'))->with('success', 'Signed in successfully. You can now leave comments.');
    }

    public function avatar()
    {
        $memberId = (int) (session('member_id') ?? 0);
        if ($memberId < 1) {
            return redirect()->to(site_url('member'))->with('error', 'Sign in to upload an avatar.');
        }

        $file = $this->request->getFile('avatar');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'Choose a valid image.');
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return redirect()->back()->with('error', 'Avatar image must be 5 MB or smaller.');
        }

        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (! in_array((string) $file->getMimeType(), $allowed, true)) {
            return redirect()->back()->with('error', 'Upload a JPEG, PNG, GIF or WebP image for the avatar.');
        }

        $tmpDir = WRITEPATH . 'uploads/member-avatar/';
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $tmpName = 'avatar-' . $memberId . '-' . bin2hex(random_bytes(6)) . '.' . $file->getExtension();
        if (! $file->move($tmpDir, $tmpName)) {
            return redirect()->back()->with('error', 'Avatar upload failed.');
        }

        $relative = 'assets/uploads/avatars/member-' . $memberId . '-' . time() . '.webp';
        $destination = FCPATH . $relative;
        $source = $tmpDir . $tmpName;
        $converted = ImageProcessor::squareWebp($source, $destination, 160, 82);
        @unlink($source);

        if (! $converted) {
            return redirect()->back()->with('error', 'Could not convert the avatar to WebP.');
        }

        $users = new UserModel();
        $user = $users->find($memberId);
        $oldAvatar = (string) ($user->avatar_path ?? '');

        $users->update($memberId, [
            'avatar_path' => $relative,
            'avatar_updated_at' => date('Y-m-d H:i:s'),
        ]);

        if ($oldAvatar !== '' && str_starts_with($oldAvatar, 'assets/uploads/avatars/')) {
            @unlink(FCPATH . $oldAvatar);
        }

        session()->set('member_avatar', $relative);

        return redirect()->back()->with('success', 'Your profile photo has been updated.');
    }


    public function profile(): string
    {
        $member = $this->requireMember();
        if ($member === null) {
            return '';
        }

        return view('member/profile', [
            'title' => 'Member profile',
            'member' => $member,
        ]);
    }

    public function updateProfile()
    {
        $member = $this->requireMember();
        if ($member === null) {
            return redirect()->to(site_url('member'));
        }

        $username = trim((string) $this->request->getPost('username'));
        if (mb_strlen($username) < 3 || mb_strlen($username) > 100) {
            return redirect()->back()->with('error', 'Display name must be 3-100 characters.');
        }

        $users = new UserModel();
        $existing = $users->where('username', $username)->where('id !=', (int) $member['id'])->first();
        if ($existing) {
            return redirect()->back()->with('error', 'That display name is already taken.');
        }

        $users->update((int) $member['id'], ['username' => $username]);
        session()->set('member_name', $username);

        return redirect()->back()->with('success', 'Profile updated.');
    }

    public function bookmarks(): string
    {
        $member = $this->requireMember();
        if ($member === null) {
            return '';
        }

        $bookmarks = (new ContentBookmarkModel())
            ->select('content_bookmarks.*, content.title, content.slug, content.published_at, content.excerpt')
            ->join('content', 'content.id = content_bookmarks.content_id')
            ->where('content_bookmarks.user_id', (int) $member['id'])
            ->where('content.status', 'published')
            ->orderBy('content_bookmarks.created_at', 'DESC')
            ->findAll(50);

        return view('member/bookmarks', [
            'title' => 'Bookmarks',
            'member' => $member,
            'bookmarks' => $bookmarks,
        ]);
    }

    protected function requireMember(): ?array
    {
        $member = $this->memberSession();
        if (! $member) {
            redirect()->to(site_url('member'))->with('error', 'Sign in to continue.')->send();
            return null;
        }
        return $member;
    }

    public function logout()
    {
        session()->remove(['member_id', 'member_email', 'member_name', 'member_avatar']);

        return redirect()->to(site_url('/'))->with('success', 'You have been signed out.');
    }

    protected function memberSession(): ?array
    {
        $id = session('member_id');
        if (! $id) {
            return null;
        }

        return [
            'id'    => (int) $id,
            'email' => (string) session('member_email'),
            'name'  => (string) session('member_name'),
            'avatar' => (string) session('member_avatar'),
        ];
    }
}

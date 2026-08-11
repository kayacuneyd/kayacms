<?php

namespace User\Libraries;

use User\Models\MagicLinkModel;
use User\Models\UserModel;

/**
 * Passwordless email sign-in links.
 *
 * Each call to issue() revokes the user's previous outstanding links so a
 * magic link is always single-use and the latest one wins. Links expire
 * after TTL_MINUTES and are consumed (marked used) on first click.
 */
class MagicLink
{
    public const TTL_MINUTES = 15;

    protected MagicLinkModel $linkModel;
    protected UserModel $userModel;

    public function __construct(?MagicLinkModel $linkModel = null, ?UserModel $userModel = null)
    {
        $this->linkModel = $linkModel ?? new MagicLinkModel();
        $this->userModel = $userModel ?? new UserModel();
    }

    /**
     * Issue a new magic link for a user. Returns the raw token.
     */
    public function issue(int $userId): string
    {
        $token = bin2hex(random_bytes(32));

        $this->linkModel->invalidateForUser($userId);

        $this->linkModel->insert([
            'user_id'    => $userId,
            'token'      => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + self::TTL_MINUTES * 60),
        ]);

        return $token;
    }

    /**
     * Absolute URL for a token (for use in emails).
     */
    public function linkFor(string $token): string
    {
        return base_url('admin/magic-link/' . $token);
    }

    /**
     * Resolve a token to its user record. Consumes the link on success so it
     * can only be used once. Returns a UserEntity or null when invalid/expired.
     */
    public function consume(string $token): ?\User\Entities\UserEntity
    {
        $link = $this->linkModel->validToken($token);

        if ($link === null) {
            return null;
        }

        $user = $this->userModel->find((int) $link['user_id']);

        if ($user === null) {
            return null;
        }

        $this->linkModel->update((int) $link['id'], ['used_at' => date('Y-m-d H:i:s')]);

        return $user;
    }

    /**
     * Whether passwordless sign-in is currently enabled (admin setting).
     */
    public function isEnabled(): bool
    {
        return (bool) (new \Setting\Models\SettingModel())->getSetting('magic_link_enabled', true);
    }
}
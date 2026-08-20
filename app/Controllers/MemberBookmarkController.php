<?php

namespace App\Controllers;

use App\Models\ContentBookmarkModel;
use Content\Models\ContentModel;

class MemberBookmarkController extends BaseController
{
    public function toggle()
    {
        $memberId = (int) (session('member_id') ?? 0);
        if ($memberId < 1) {
            return $this->respondBookmark(false, false, 'Sign-in required.', 401);
        }

        $contentId = (int) $this->request->getPost('content_id');
        $content = (new ContentModel())->where('status', 'published')->find($contentId);
        if (! $content) {
            return $this->respondBookmark(false, false, 'Content not found.', 404);
        }

        $bookmarked = (new ContentBookmarkModel())->toggle($memberId, $contentId);

        return $this->respondBookmark(true, $bookmarked, $bookmarked ? 'Saved.' : 'Removed from bookmarks.');
    }

    private function respondBookmark(bool $ok, bool $bookmarked, string $message, int $status = 200)
    {
        $accept = (string) $this->request->getHeaderLine('Accept');
        if (str_contains($accept, 'application/json') || $this->request->isAJAX()) {
            return $this->response->setStatusCode($status)->setJSON([
                'ok' => $ok,
                'bookmarked' => $bookmarked,
                'message' => $message,
                'csrf' => [
                    'name' => csrf_token(),
                    'hash' => csrf_hash(),
                ],
            ]);
        }

        return redirect()->back()->with($ok ? 'success' : 'error', $message);
    }
}

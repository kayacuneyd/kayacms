<?php

namespace User\Controllers\Api;

use App\Core\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use User\Libraries\ApiToken;

class TokenController extends BaseController
{
    /**
     * List the caller's personal access tokens.
     */
    public function index(): ResponseInterface
    {
        $tokens = (new ApiToken())->forUser((int) $this->request->user->id);

        // Never expose the hash; only metadata.
        $tokens = array_map(static function ($t) {
            unset($t['token_hash']);

            return $t;
        }, $tokens);

        return $this->respond(['tokens' => $tokens]);
    }

    /**
     * Issue a new token. The plaintext is returned only once.
     */
    public function store(): ResponseInterface
    {
        $rules = [
            'name'    => 'required|min_length[3]|max_length[100]',
            'scopes'  => 'permit_empty',
            'expires_in' => 'permit_empty|is_natural',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $scopes = $this->request->getJsonVar('scopes');
        $scopes = is_array($scopes) ? array_filter(array_map('strval', $scopes)) : [];

        $expires = (int) $this->request->getJsonVar('expires_in');
        $expires = $expires > 0 ? $expires : null;

        $issued = (new ApiToken())->create(
            (int) $this->request->user->id,
            (string) $this->request->getJsonVar('name'),
            $scopes,
            $expiresIn
        );

        return $this->respond([
            'token_id' => $issued['id'],
            'plain_token' => $issued['plain'],
            'message' => 'Copy this token now. You will not see it again.',
        ], 201);
    }

    /**
     * Revoke a token.
     */
    public function revoke(int $id): ResponseInterface
    {
        $api = new ApiToken();

        $rows = $api->forUser((int) $this->request->user->id);
        $owned = array_filter($rows, static fn ($t) => (int) $t['id'] === $id);

        if (! $owned) {
            return $this->failNotFound('Token not found');
        }

        $api->revoke($id);

        return $this->respond(['message' => 'Token revoked']);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;

class OauthController extends Controller
{
    public function revokeAccessToken(Request $request) {
        $tokenId = $request->user()->token()->id;

        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
        
        // Revoke an access token...
        $tokenRepository->revokeAccessToken($tokenId);
        
        // Revoke all of the token's refresh tokens...
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($tokenId);
    }
}

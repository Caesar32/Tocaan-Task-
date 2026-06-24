<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthService
{
    /**
     * Register a new user and generate a JWT token.
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Authenticate a user and return a JWT token.
     *
     * @return array{user: User, token: string}
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function login(array $credentials): array
    {
        $token = JWTAuth::attempt($credentials);

        if (! $token) {
            throw new \Illuminate\Auth\AuthenticationException('Invalid credentials');
        }

        $user = JWTAuth::setToken($token)->user();

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Get the currently authenticated user.
     */
    public function getAuthenticatedUser(): User
    {
        return JWTAuth::parseToken()->user();
    }

    /**
     * Invalidate the current JWT token (logout).
     */
    public function logout(): void
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    /**
     * Refresh the current JWT token.
     *
     * @return array{user: User, token: string}
     */
    public function refreshToken(): array
    {
        $token = JWTAuth::parseToken()->refresh();
        $user = Auth::guard('jwt')->user();

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}

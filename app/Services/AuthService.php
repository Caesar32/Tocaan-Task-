<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

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
     * @throws AuthenticationException
     */
    public function login(array $credentials): array
    {
        $token = JWTAuth::attempt($credentials);

        if (! $token) {
            throw new AuthenticationException('Invalid credentials');
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
        $user = Auth::guard('jwt')->user();

        if (! $user) {
            throw new AuthenticationException('Unauthenticated');
        }

        return $user;
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

<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->successResponse('User registered successfully', [
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 201);
    }

    /**
     * Authenticate a user and return JWT token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->only('email', 'password'));

            return $this->successResponse('Login successful', [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ]);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return $this->errorResponse(
                'Invalid credentials',
                401
            );
        }
    }

    /**
     * Get the authenticated user's profile.
     */
    public function me(): JsonResponse
    {
        $user = $this->authService->getAuthenticatedUser();

        return $this->successResponse('User profile retrieved successfully', [
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Logout the authenticated user (invalidate token).
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->successResponse('User logged out successfully');
    }

    /**
     * Refresh the JWT token.
     */
    public function refresh(): JsonResponse
    {
        $result = $this->authService->refreshToken();

        return $this->successResponse('Token refreshed successfully', [
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ]);
    }
}

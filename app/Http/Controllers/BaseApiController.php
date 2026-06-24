<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    /**
     * Build a success JSON response.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @param  int  $statusCode
     */
    protected function successResponse(string $message, mixed $data = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Build an error JSON response.
     *
     * @param  string  $message
     * @param  int  $statusCode
     * @param  mixed  $errors
     */
    protected function errorResponse(string $message, int $statusCode = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}

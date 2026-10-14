<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * One JSON shape for every API response:
 *   { "success": bool, "data": mixed|null, "message": string, ["meta": {...}], ["errors": {...}] }
 */
final class ApiResponse
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public static function success(mixed $data = null, string $message = 'OK', int $status = 200, array $meta = []): JsonResponse
    {
        $body = ['success' => true, 'data' => $data, 'message' => $message];

        if ($meta !== []) {
            $body['meta'] = $meta;
        }

        return response()->json($body, $status);
    }

    /**
     * @param  array<string, mixed>|null  $errors  field-level validation messages (422)
     */
    public static function error(string $message, int $status, ?array $errors = null): JsonResponse
    {
        $body = ['success' => false, 'data' => null, 'message' => $message];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status);
    }
}

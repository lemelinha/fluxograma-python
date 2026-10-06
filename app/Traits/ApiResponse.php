<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Resposta de Sucesso
     */
    protected function successResponse(int $statusCode = 200, mixed $data = null, ?string $message = null): JsonResponse {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Resposta de Erro
     */
    protected function errorResponse(string $message, int $statusCode, mixed $details = null, ?string $code = null): JsonResponse {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'details' => $details,
            'code' => $code
        ], $statusCode);
    }
}

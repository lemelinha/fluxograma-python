<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AstToPythonException extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): void
    {
        $text = 'Erro em gerar Python a partir da AST: ';

        Log::error($text, ['exception' => $this]);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
        ], 422);
    }
}

<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FlowchartToAstException extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): void
    {
        $text = "Erro em converter o fluxograma para Ast: ";
        
        Log::error($text, ["exception" => $this]);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            "status" => 'error',
            "message" => $this->getMessage()
        ], 422);
    }
}

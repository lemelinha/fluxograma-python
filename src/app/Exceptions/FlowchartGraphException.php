<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FlowchartGraphException extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): void
    {
        $text = "Erro em gerar Grafo a partir do fluxograma: " . $this->getMessage();
        foreach ($this->getTrace() as $t) {
            $text .= $t;
        }
        Log::error($text);
    }

    /**
     * Render the exception as an JSON response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            "status" => 'error',
            "message" => $this->getMessage()
        ], 406);
    }
}

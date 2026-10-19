<?php

namespace App\Http\Controllers;

use App\Convert\FlowchartGraph;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConvertController extends Controller
{
    use ApiResponse;

    /**
     * Controller para a conversão
     */
    public function convert(string $sourceLanguage, string $targetLanguage, Request $request): JsonResponse
    {
        if (! in_array($sourceLanguage, config('convert.sourceLanguages'))) {
            return $this->errorResponse('Linguagem de entrada inválida', 400, code: 'INVALID_SOURCE_LANGUANGE');
        }

        if (! in_array($targetLanguage, config('convert.targetLanguages'))) {
            return $this->errorResponse('Linguagem de saída inválida', 400, code: 'INVALID_TARGET_LANGUANGE');
        }

        $validated = $request->validate([
            'nodes' => 'array|required',
            'edges' => 'array|required',
        ]);

        $nodes = $validated['nodes'];
        $edges = $validated['edges'];

        try {
            $graph = (new FlowchartGraph)->toGraph($nodes, $edges);
            $graph->validate();
            $ast = $graph->toAst();
            $code = $ast->toPython();
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                400,
            );
        }

        return $this->successResponse(200, ['code' => $code], 'tudo ok!!!');
    }
}

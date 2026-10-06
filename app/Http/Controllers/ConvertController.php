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
        if (!in_array($sourceLanguage, config('convert.sourceLanguages'))) {
            return $this->errorResponse('Linguagem de entrada inválida', 400, code: 'INVALID_SOURCE_LAGUANGE');
        }

        if (!in_array($targetLanguage, config('convert.targetLanguages'))) {
            return $this->errorResponse('Linguagem de saída inválida', 400, code: 'INVALID_TARGET_LAGUANGE');
        }

        //$validated = $request->validate([
        //    'nodes' => 'array|required',
        //    'edges' => 'array|required'
        //]);

        $nodes = [
            [
                'id' => 1,
                'type' => 'start'
            ],
            [
                'id' => 2,
                'type' => 'input',
                'data' => [
                    'varName' => 'nome',
                    'varType' => 'str',
                    'label' => 'Digite seu nome'
                ]
            ],
            [
                'id' => 3,
                'type' => 'output',
                'data' => [
                    'expression' => [
                        'type' => 'var',
                        'value' => 'nome'
                    ]
                ]
            ],
            [
                'id' => 4,
                'type' => 'end'
            ]
        ];//$validated['nodes'];
        $edges = [
            [
                'source' => 1,
                'target' => 2,
                'label' => null
            ],
            [
                'source' => 2,
                'target' => 3,
                'label' => null
            ],
            [
                'source' => 3,
                'target' => 4,
                'label' => null
            ]
        ];//$validated['edges'];

        try {
            $graph = (new FlowchartGraph)->toGraph($nodes, $edges);
        } catch (Exception $th) {
            dd($th);
        }

        return $this->successResponse(200, ['code' => 'código'], 'tudo ok!!!');
    }
}

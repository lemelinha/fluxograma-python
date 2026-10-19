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
            return $this->errorResponse('Linguagem de entrada inválida', 400, code: 'INVALID_SOURCE_LANGUANGE');
        }

        if (!in_array($targetLanguage, config('convert.targetLanguages'))) {
            return $this->errorResponse('Linguagem de saída inválida', 400, code: 'INVALID_TARGET_LANGUANGE');
        }

        //$validated = $request->validate([
        //    'nodes' => 'array|required',
        //    'edges' => 'array|required'
        //]);

        $nodes = [
            [
                'id' => '1',
                'type' => 'start',
                'position' => [
                    'x' => 100,
                    'y' => 100
                ],
                'data' => [],
                'deletable' => false,
                'measured' => [
                    'width' => 120,
                    'height' => 50
                ]
            ],
            [
                'id' => '61057029-0d16-4c51-8fd7-2417b846aa73',
                'type' => 'input',
                'position' => [
                    'x' => 30.237957613269188,
                    'y' => 367.66087096545823
                ],
                'data' => [
                    'variable' => 'b',
                    'dataType' => 'int',
                    'label' => 'aaa'
                ],
                'measured' => [
                    'width' => 150,
                    'height' => 115
                ],
                'selected' => false,
                'dragging' => false
            ],
            [
                'id' => '77a3994e-9452-4cba-870a-47c040f1f6b9',
                'type' => 'input',
                'position' => [
                    'x' => 72.54147187708705,
                    'y' => 206.5182276670985
                ],
                'data' => [
                    'variable' => 'a',
                    'dataType' => 'int',
                    'label' => 'bbb'
                ],
                'measured' => [
                    'width' => 150,
                    'height' => 115
                ],
                'selected' => true,
                'dragging' => false
            ],
            [
                'id' => '6e7d83f0-9a20-4d13-bfd9-70d3ccc308a1',
                'type' => 'process',
                'position' => [
                    'x' => 0.1449807733151829,
                    'y' => 534.246992758061
                ],
                'data' => [
                    'variable' => 'soma',
                    'expression' => 'a + b'
                ],
                'measured' => [
                    'width' => 160,
                    'height' => 95
                ],
                'selected' => false,
                'dragging' => false
            ],
            [
                'id' => '109db0ca-028e-43eb-835e-11b2185c6488',
                'type' => 'output',
                'position' => [
                    'x' => -76.16221049942546,
                    'y' => 651.3946525993106
                ],
                'data' => [
                    'expression' => 'soma'
                ],
                'measured' => [
                    'width' => 150,
                    'height' => 70
                ],
                'selected' => false,
                'dragging' => false
            ],
            [
                'id' => '03417e3f-67b9-4fe5-a586-dca26a0ca04d',
                'type' => 'end',
                'position' => [
                    'x' => 3.3692282918816545,
                    'y' => 742.7483322920282
                ],
                'data' => [
                    'label' => 'Fim'
                ],
                'measured' => [
                    'width' => 120,
                    'height' => 50
                ],
                'selected' => false,
                'dragging' => false
            ]
        ];//$validated['nodes'];
        $edges = [
            [
                'source' => '1',
                'target' => '77a3994e-9452-4cba-870a-47c040f1f6b9',
                'id' => 'xy-edge__1-77a3994e-9452-4cba-870a-47c040f1f6b9'
            ],
            [
                'source' => '77a3994e-9452-4cba-870a-47c040f1f6b9',
                'target' => '61057029-0d16-4c51-8fd7-2417b846aa73',
                'id' => 'xy-edge__77a3994e-9452-4cba-870a-47c040f1f6b9-61057029-0d16-4c51-8fd7-2417b846aa73'
            ],
            [
                'source' => '61057029-0d16-4c51-8fd7-2417b846aa73',
                'target' => '6e7d83f0-9a20-4d13-bfd9-70d3ccc308a1',
                'id' => 'xy-edge__61057029-0d16-4c51-8fd7-2417b846aa73-6e7d83f0-9a20-4d13-bfd9-70d3ccc308a1'
            ],
            [
                'source' => '6e7d83f0-9a20-4d13-bfd9-70d3ccc308a1',
                'target' => '109db0ca-028e-43eb-835e-11b2185c6488',
                'id' => 'xy-edge__6e7d83f0-9a20-4d13-bfd9-70d3ccc308a1-109db0ca-028e-43eb-835e-11b2185c6488'
            ],
            [
                'source' => '109db0ca-028e-43eb-835e-11b2185c6488',
                'target' => '03417e3f-67b9-4fe5-a586-dca26a0ca04d',
                'id' => 'xy-edge__109db0ca-028e-43eb-835e-11b2185c6488-03417e3f-67b9-4fe5-a586-dca26a0ca04d'
            ]
        ];//$validated['edges'];

        try {
            $graph = (new FlowchartGraph)->toGraph($nodes, $edges);
            $graph->validate();
            $ast = $graph->toAst();
            dd($ast->toPython());
        } catch (Exception $e) {
            return $this->errorResponse(
                $e->getMessage(),
                400,
            );
        }

        return $this->successResponse(200, ['code' => 'código'], 'tudo ok!!!');
    }
}

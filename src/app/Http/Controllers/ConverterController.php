<?php

namespace App\Http\Controllers;

use App\CodeGenerator\Python\AstToPython;
use App\Flowchart\FlowchartGraph;
use App\Flowchart\FlowchartGraphToAst;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConverterController extends Controller
{
    public function convert(Request $request, string $source, string $target): JsonResponse
    {
        if ($source === $target) {
            return response()->json(['status' => 'error', 'message' => 'source e target são iguais'], 400);
        }

        $validated = $request->validate([
            'nodes' => 'required|array',
            'edges' => 'required|array',
        ]);

        //$json = json_decode(file_get_contents(__DIR__.'/../../../tests/Fixtures/flowchart/codeConversionTests/11-comples-operations.json'), true);

        $nodes = $validated['nodes'];
        $edges = $validated['edges'];

        switch ($source) {
            case 'flowchart':
                $graph = new FlowchartGraph($nodes, $edges);
                $graph->validate();

                $ast = FlowchartGraphToAst::convert($graph);
                break;
            case 'python':
                return response()->json([
                    'status' => 'error',
                    'message' => "Conversão python para $target ainda não implementada",
                ], 400);
            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Algo deu errado!',
                ], 400);
        }

        switch ($target) {
            case 'python':
                $code = AstToPython::convert($ast);
                break;
            case 'flowchart':
                return response()->json([
                    'status' => 'error',
                    'message' => "Conversão $source para fluxograma ainda não implementada",
                ], 400);
            default:
                return response()->json([
                    'status' => 'error',
                    'message' => 'Algo deu errado!',
                ], 400);
        }

        return response()->json([
            'status' => 'ok',
            'code' => $code,
        ]);
    }
}

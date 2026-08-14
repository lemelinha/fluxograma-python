<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FlowchartToAstService;
use App\Flowchart\FlowchartGraph;

class ConverterController extends Controller
{
    public function __construct(
        protected FlowchartToAstService $FlowchartToAstService
    ){}

    public function convert(Request $request, string $source, string $target)
    {
        // example of nodes and edges hardcode por enquanto
        $nodes = [
            [
                "id" => "1",
                "type" => "init",
                "position" => ["x" => 0, "y" => 0],
                "data" => [
                    "label" => "Inicio"
                ],
            ],
            [
                "id" => "2",
                "type" => "input",
                "position" => ["x" => 0, "y" => 0],
                "data" => [
                    "label" => "Ler variável A",
                    "varName" => "a",
                    "varType" => "int"
                ],
            ],
            [
                "id" => "3",
                "type" => "input",
                "position" => ["x" => 0, "y" => 100],
                "data" => [
                    "label" => "Ler variável B",
                    "varName" => "b",
                    "varType" => "int"
                ],
            ],
            [
                "id" => "4",
                "type" => "operation",
                "position" => ["x" => 0, "y" => 200],
                "data" => [
                    "label" => "Somar A + B",
                    "varName" => "resultado",
                    "operator" => "+",
                    "left" => ["type" => "var", "value" => "a"],
                    "right" => ["type" => "var", "value" => "b"]
                ],
            ],
            [
                "id" => "5",
                "type" => "output",
                "position" => ["x" => 0, "y" => 300],
                "data" => [
                    "label" => "Exibir resultado",
                    "expression" => ["type" => "var", "value" => "resultado"],
                ],
            ],
            [
                "id" => "6",
                "type" => "end",
                "position" => ["x" => 0, "y" => 0],
                "data" => [
                    "label" => "Fim"
                ],
            ],
        ];
        $edges = [
            ["id" => "e1-2", "source" => "1", "target" => "2"],
            ["id" => "e2-3", "source" => "2", "target" => "3"],
            ["id" => "e3-4", "source" => "3", "target" => "4"],
            ["id" => "e4-5", "source" => "4", "target" => "5"],
            ["id" => "e5-6", "source" => "5", "target" => "6"],
        ];    
    
        switch ($source) {
            case 'flowchart':
                $graph = new FlowchartGraph($nodes, $edges);
                $graph->validate();
                $ast = $this->FlowchartToAstService->convert($graph);
                dd($ast);
                break;
            case "python":
                break;
        }

        return response()->json(['status' => 'ok', 'message' => 'tudo ok']);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FlowchartToAstService;

class ConverterController extends Controller
{
    public function __construct(
        protected FlowchartToAstService $FlowchartToAstService
    ){}

    public function convert(Request $request, string $source, string $target)
    {
        // example of nodes and edges
        $nodes = [
            ["id" => "1", ""]
        ];
        $edges = [];    
    
        $ast='';
        switch ($source) {
            case 'flowchart':
                $ast = $this->FlowchartToAstService->convert($nodes, $edges);
        }

        return response()->json($ast);
    }
}

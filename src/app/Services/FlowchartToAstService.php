<?php

namespace App\Services;

use App\Ast\{
    Ast, 
    InitStatement,
    InputStatement,
    OutputStatement,
    Statement
    };
use App\Flowchart\FlowchartGraph;

class FlowchartToAstService {
    public function convert(FlowchartGraph $graph): Ast{
        $initNode = collect($graph->nodes)->filter(fn(array $n)=>$n["type"]=="init");
        $initId = $initNode->first()["id"];

        $ast = new Ast($this->walkGraph($graph, $initId));

        return $ast;
    }

    /** @return list<Statement> */
    protected function walkGraph(FlowchartGraph $graph, int|string $from) {
        $visited = [];
        
        $statements = [];
        $nodeId = $from;
        while ($nodeId !== null && !isset($visited[$nodeId])) {
            $visited[$nodeId] = true;
            $cur_node = $graph->nodes[$nodeId];
            if ($cur_node["type"] == "end") break;

            $statements[] = match ($cur_node["type"]) {
                "init" => $this->AddInitStatement(),
                "input" => $this->AddInputStatement($cur_node),
                default => dd($statements)
            };

            $nextId = $graph->outGoing[$nodeId][0]["target"];
            $nodeId = $nextId;
        }

        return $statements;
    }

    protected function AddInitStatement(): InitStatement {
        return new InitStatement();
    }

    protected function AddInputStatement(array $node) {
        $data = $node["data"];
        return new InputStatement(
            $data["type"],
            $data["varName"]
        );
    }
}

<?php

namespace App\Services;

use App\Ast\{
    AssignmentStatement,
    Ast,
    BinaryExpression,
    InitStatement,
    InputStatement,
    OutputStatement,
    Literal,
    Statement,
    Variable
};
use App\Exceptions\FlowchartToAstException;
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
                "end" => $this->AddEndStatement(),
                "input" => $this->AddInputStatement($cur_node),
                "output" => $this->AddOutputStatement($cur_node),
                "operation" => $this->AddOperation($cur_node),
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

    protected function AddEndStatement(): InitStatement {
        return new InitStatement();
    }

    protected function AddInputStatement(array $node): AssignmentStatement|InputStatement {
        $data = $node["data"];
        $varName = $data["varName"];
        $varType = $data["type"];

        $expression = new Literal("string", $data["label"]);
        $input = new InputStatement($varType, $expression);

        if (!isset($this->variables[$varName])) {
            $this->variables[$varName] = new Variable($varType, $varName);
        }

        return new AssignmentStatement($varName, $input);
    }

    protected function AddOutputStatement(array $node): OutputStatement {
        $data = $node["data"];
        $outputType = $data["expression"]["type"];
        $outputValue = $data["expression"]["value"];

        $expression = match ($outputType) {
            "var" => $this->variables[$outputValue]?? throw new FlowchartToAstException("Variable $outputValue not declared")
        };

        return new OutputStatement($expression);

    }

    protected function AddOperation(array $node): AssignmentStatement {
        $data = $node["data"];

        $leftType = $data["left"]["type"];
        $leftValue = $data["left"]["value"];
        $rightType = $data["right"]["type"];
        $rightValue = $data["right"]["value"];

        $left = match ($leftType) {
            "var" => $this->variables[$leftValue]?? throw new FlowchartToAstException("Variable $leftValue not declared"),
            default => throw new FlowchartToAstException("Invalid var type in operation")
        };

        $right = match ($rightType) {
            "var" => $this->variables[$rightValue]?? new FlowchartToAstException("Variable $rightValue not declared"),
            default => throw new FlowchartToAstException("Invalid var type in operation")
        };

        $binaryExpression = new BinaryExpression(
            $data["operator"],
            $left,
            $right
        );

        if (!isset($this->variables[$data["varName"]])) {
            $this->variables[$data["varName"]] = new Variable("", $data["varName"]);
        }

        return new AssignmentStatement(
            $data["varName"],
            $binaryExpression
        );
    }
}

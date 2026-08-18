<?php

namespace App\Flowchart;

use App\Ast\{
    AssignmentStatement,
    Ast,
    BinaryExpression,
    EndStatement,
    InitStatement,
    InputExpression,
    OutputStatement,
    Literal,
    Statement,
    Variable
};
use App\Exceptions\FlowchartToAstException;
use App\Flowchart\FlowchartGraph;

class FlowchartToAst {
    public static function convert(FlowchartGraph $graph): Ast{
        $initNode = collect($graph->nodes)->filter(fn(array $n)=>$n["type"]=="init");
        $initId = $initNode->first()["id"];

        $ast = new Ast(self::walkGraph($graph, $initId));

        return $ast;
    }

    /** @return list<Statement> */
    protected static function walkGraph(FlowchartGraph $graph, int|string $from): array {
        $visited = [];
        
        $statements = [];
        $nodeId = $from;
        while ($nodeId !== null && !isset($visited[$nodeId])) {
            $visited[$nodeId] = true;
            $cur_node = $graph->nodes[$nodeId];
            
            $statements[] = match ($cur_node["type"]) {
                "init" => self::AddInitStatement(),
                "end" => self::AddEndStatement(),
                "input" => self::AddInputExpression($cur_node),
                "output" => self::AddOutputStatement($cur_node),
                "operation" => self::AddOperation($cur_node),
                default => throw new FlowchartToAstException('Invalid node type')
            };

            if ($cur_node["type"] == "end") break;
            $nextId = $graph->outGoing[$nodeId][0]["target"];
            $nodeId = $nextId;
        }

        return $statements;
    }

    protected static function AddInitStatement(): InitStatement {
        return new InitStatement();
    }

    protected static function AddEndStatement(): EndStatement {
        return new EndStatement();
    }

    protected static function AddInputExpression(array $node): AssignmentStatement|InputExpression {
        $data = $node["data"];
        $varName = $data["varName"];
        $varType = $data["varType"];

        $expression = new Literal($data["label"]);
        $input = new InputExpression($varType, $expression);

        return new AssignmentStatement($varName, $input);
    }

    protected static function AddOutputStatement(array $node): OutputStatement {
        $data = $node["data"];
        $outputType = $data["expression"]["type"];
        $outputValue = $data["expression"]["value"];

        $expression = match ($outputType) {
            "var" => new Variable($outputValue)
        };

        return new OutputStatement($expression);

    }

    protected static function AddOperation(array $node): AssignmentStatement {
        $data = $node["data"];

        $leftType = $data["left"]["type"];
        $leftValue = $data["left"]["value"];
        $rightType = $data["right"]["type"];
        $rightValue = $data["right"]["value"];

        $left = match ($leftType) {
            "var" => new Variable($leftValue),
            default => throw new FlowchartToAstException("Invalid var type in operation")
        };

        $right = match ($rightType) {
            "var" => new Variable($rightValue),
            default => throw new FlowchartToAstException("Invalid var type in operation")
        };

        $binaryExpression = new BinaryExpression(
            $data["operator"],
            $left,
            $right
        );

        return new AssignmentStatement(
            $data["varName"],
            $binaryExpression
        );
    }
}

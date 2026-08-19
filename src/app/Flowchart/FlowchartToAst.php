<?php

namespace App\Flowchart;

use App\Ast\Ast;
use App\Ast\Expressions\BinaryExpression;
use App\Ast\Expressions\Expression;
use App\Ast\Expressions\InputExpression;
use App\Ast\Expressions\Literal;
use App\Ast\Expressions\Variable;
use App\Ast\Statements\AssignmentStatement;
use App\Ast\Statements\EndStatement;
use App\Ast\Statements\InitStatement;
use App\Ast\Statements\OutputStatement;
use App\Ast\Statements\Statement;
use App\Exceptions\FlowchartToAstException;

class FlowchartToAst
{
    public static function convert(FlowchartGraph $graph): Ast
    {
        $initNode = collect($graph->nodes)->filter(fn (array $n) => $n['type'] == 'init');
        $initId = $initNode->first()['id'];

        $ast = new Ast(self::walkGraph($graph, $initId));

        return $ast;
    }

    /** @return list<Statement> */
    protected static function walkGraph(FlowchartGraph $graph, int|string $from): array
    {
        $visited = [];

        $statements = [];
        $nodeId = $from;
        while ($nodeId !== null && ! isset($visited[$nodeId])) {
            $visited[$nodeId] = true;
            $cur_node = $graph->nodes[$nodeId];

            $statements[] = match ($cur_node['type']) {
                'init' => self::AddInitStatement(),
                'end' => self::AddEndStatement(),
                'input' => self::AddInputExpression($cur_node),
                'output' => self::AddOutputStatement($cur_node),
                'operation' => self::AddOperation($cur_node),
                default => throw new FlowchartToAstException('Invalid node type')
            };

            if ($cur_node['type'] == 'end') {
                break;
            }
            $nextId = $graph->outGoing[$nodeId][0]['target'];
            $nodeId = $nextId;
        }

        return $statements;
    }

    protected static function AddInitStatement(): InitStatement
    {
        return new InitStatement;
    }

    protected static function AddEndStatement(): EndStatement
    {
        return new EndStatement;
    }

    protected static function AddInputExpression(array $node): AssignmentStatement
    {
        $data = $node['data'];
        $varName = $data['varName'];
        $varType = $data['varType'];

        $expression = new Literal($data['label']);
        $input = new InputExpression($varType, $expression);

        return new AssignmentStatement($varName, $input);
    }

    protected static function AddOutputStatement(array $node): OutputStatement
    {
        $data = $node['data'];
        $outputType = $data['expression']['type'];
        $outputValue = $data['expression']['value'];

        $expression = match ($outputType) {
            'var' => new Variable($outputValue),
            'text' => new Literal($outputValue),
            default => throw new FlowchartToAstException('Expression type inesperado')
        };

        return new OutputStatement($expression);

    }

    protected static function AddOperation(array $node): AssignmentStatement
    {
        $data = $node['data'];

        $binaryExpression = new BinaryExpression(
            $data['operator'],
            self::parseOperand($data['left']),
            self::parseOperand($data['right'])
        );

        return new AssignmentStatement(
            $data['varName'],
            $binaryExpression
        );
    }

    protected static function parseOperand(array $operand): Expression
    {
        $type = $operand['type'];

        if ($type == 'var' && isset($operand['value'])) {
            return new Variable($operand['value']);
        }

        if ($type == 'literal' && isset($operand['value'])) {
            return new Literal((string) $operand['value']);
        }

        if (
            $type == 'binary'
            && isset($operand['operator'], $operand['left'], $operand['right'])
        ) {
            return new BinaryExpression(
                $operand['operator'],
                self::parseOperand($operand['left']),
                self::parseOperand($operand['right'])
            );
        }

        throw new FlowchartToAstException('Operador Binário Invalido');
    }
}

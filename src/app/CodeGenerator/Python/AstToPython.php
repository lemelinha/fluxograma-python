<?php

namespace App\CodeGenerator\Python;

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
use RuntimeException;

class AstToPython
{
    public static function convert(Ast $ast): array
    {
        $code = [];
        $statements = $ast->statements;

        foreach ($statements as $statement) {
            if ($statement instanceof InitStatement || $statement instanceof EndStatement) {
                continue;
            }

            $line = '';
            if ($statement instanceof AssignmentStatement) {
                $line = self::AssignmentHandler($statement);
            }
            if ($statement instanceof OutputStatement) {
                $line = self::OutputPrinter($statement);
            }

            $code[] = $line;
        }

        return $code;
    }

    protected static function AssignmentHandler(AssignmentStatement $assignment): string
    {
        $varName = $assignment->varName;
        $expression = $assignment->expression;

        $line = "$varName = ";
        if (
            $expression instanceof Literal ||
            $expression instanceof Variable
        ) {
            $line .= $expression;
        } elseif ($expression instanceof InputExpression) {
            $line .= self::InputPrinter($expression);
        } elseif ($expression instanceof BinaryExpression) {
            $line .= self::BinaryExpressionPrinter($expression);
        }

        return $line;
    }

    protected static function InputPrinter(InputExpression $input): string
    {
        $inputType = $input->type;
        $inputExpression = $input->expression;

        return "$inputType(input('$inputExpression'))";
    }

    protected static function OutputPrinter(OutputStatement $output): string
    {
        $line = '';

        $outputValue = $output->value;
        if ($outputValue instanceof Variable) {
            $line .= "print($outputValue)";
        } else if ($outputValue instanceof Literal) {
            $line .= "print('$outputValue')";
        }

        return $line;
    }

    protected static function BinaryExpressionPrinter(BinaryExpression $expression): string
    {
        $left = self::printOperand($expression->left);
        $right = self::printOperand($expression->right);

        return "($left {$expression->operator} $right)";
    }

    protected static function printOperand(Expression $expression): string
    {
        if ($expression instanceof BinaryExpression) {
            return self::BinaryExpressionPrinter($expression);
        }

        if ($expression instanceof Variable || $expression instanceof Literal) {
            return (string) $expression;
        }

        if ($expression instanceof InputExpression) {
            return self::InputPrinter($expression);
        }

        throw new RuntimeException('Unsupported expression in binary operation');
    }
}

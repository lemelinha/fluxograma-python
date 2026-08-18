<?php

namespace App\CodeGenerator\Python;

use App\Ast\AssignmentStatement;
use App\Ast\Ast;
use App\Ast\BinaryExpression;
use App\Ast\EndStatement;
use App\Ast\InitStatement;
use App\Ast\InputExpression;
use App\Ast\Literal;
use App\Ast\OutputStatement;
use App\Ast\Variable;

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
        if (
            $outputValue instanceof Literal ||
            $outputValue instanceof Variable
        ) {
            $line .= "print($outputValue)";
        }

        return $line;
    }

    protected static function BinaryExpressionPrinter(BinaryExpression $expression): string
    {
        $line = '';
        $left = $expression->left;
        $right = $expression->right;

        if (
            $left instanceof Variable ||
            $left instanceof Literal
        ) {
            $line .= "($left)";
        } elseif ($left instanceof InputExpression) {
            $line .= self::InputPrinter($left);
        }

        $line .= " $expression->operator ";

        if (
            $right instanceof Variable ||
            $right instanceof Literal
        ) {
            $line .= "($right)";
        } elseif ($right instanceof InputExpression) {
            $line .= self::InputPrinter($right);
        }

        return $line;
    }
}

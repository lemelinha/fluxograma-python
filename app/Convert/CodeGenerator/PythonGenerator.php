<?php

namespace App\Convert\CodeGenerator;

use App\Convert\Ast\Program;
use App\Convert\Ast\Statement;
use App\Convert\Ast\Start;
use App\Convert\Ast\End;
use App\Convert\Ast\Input;
use App\Convert\Ast\Output;
use App\Convert\Ast\Assignment;
use App\Convert\Ast\Variable;
use App\Convert\Ast\Literal;
use App\Exceptions\FlowchartException;

class PythonGenerator
{
    public function generate(Program $program): string
    {
        $code = [];

        $indentation = "";

        foreach ($program->statements as $statement)
        {
            $code[] = $indentation . $this->generateStatement($statement);
        }

        return implode(PHP_EOL, $code) . PHP_EOL;
    }

    private function generateStatement(Statement $statement): string
    {
        if ($statement instanceof Start || $statement instanceof End) 
        {
            return '';
        }

        return match($statement::class)
        {
            Output::class => $this->generateOutput($statement),
            Assignment::class => $this->generateAssignment($statement),
            default => throw new FlowchartException("Statement não suportado: " . $statement::class)
        };
    }

    private function generateOutput(Output $output): string
    {
        $expression = $this->generateExpression($output->expression);
        return "print({$expression})";
    }

    private function generateAssignment(Assignment $assignment): string
    {
        $var = $assignment->variable->name;
        $expression = $this->generateExpression($assignment->expression);

        return "{$var} = {$expression}";
    }

    private function generateExpression(Statement $expression) 
    {
        switch ($expression)
        {
            case $expression instanceof Literal:
                return $this->generateLiteral($expression);
            case $expression instanceof Variable:
                return $this->generateVariable($expression);
            case $expression instanceof Input:
                return $this->generateInput($expression);
            default:
                throw new FlowchartException("Statement não suportado: " . $expression::class);
        }
    }

    private function generateLiteral(Literal $literal): string
    {
        $str = $literal->value;
        $type = $literal->type;
        
        if ($type == 'str')
        {
            return "\"{$str}\"";
        }

        return (string) $str;
    }

    private function generateVariable(Variable $variable): string
    {
        return $variable->name;
    }

    private function generateInput(Input $input): string
    {
        $type = $input->type;
        return "{$type}(input('{$input->text} '))";
    }
}

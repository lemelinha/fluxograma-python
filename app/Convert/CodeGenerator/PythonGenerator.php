<?php

namespace App\Convert\CodeGenerator;

use App\Convert\Ast\Assignment;
use App\Convert\Ast\End;
use App\Convert\Ast\IfStatement;
use App\Convert\Ast\Input;
use App\Convert\Ast\Literal;
use App\Convert\Ast\Output;
use App\Convert\Ast\Process;
use App\Convert\Ast\Program;
use App\Convert\Ast\Start;
use App\Convert\Ast\Statement;
use App\Convert\Ast\Variable;
use App\Exceptions\FlowchartException;

class PythonGenerator
{
    public function generate(Program $program): string
    {
        $code = [];

        foreach ($program->statements as $statement) {
            $stmtCode = $this->generateStatement($statement, 0);
            if ($stmtCode !== '') {
                $code[] = $stmtCode;
            }
        }

        return implode(PHP_EOL, $code).PHP_EOL;
    }

    private function generateStatement(Statement $statement, int $level = 0): string
    {
        if ($statement instanceof Start || $statement instanceof End) {
            return '';
        }

        $indent = str_repeat('    ', $level);

        return match ($statement::class) {
            Output::class => $indent.$this->generateOutput($statement),
            Assignment::class => $indent.$this->generateAssignment($statement),
            Process::class => $indent.$this->generateProcess($statement),
            IfStatement::class => $this->generateIf($statement, $level),
            default => throw new FlowchartException('Statement não suportado: '.$statement::class)
        };
    }

    private function generateOutput(Output $output): string
    {
        if (is_string($output->expression)) {
            return "print({$output->expression})";
        }

        $expression = $this->generateExpression($output->expression);

        return "print({$expression})";
    }

    private function generateAssignment(Assignment $assignment): string
    {
        $var = $assignment->variable->name;
        $expression = $this->generateExpression($assignment->expression);

        return "{$var} = {$expression}";
    }

    private function generateExpression(Statement $expression): string
    {
        return match ($expression::class) {
            Literal::class => $this->generateLiteral($expression),
            Variable::class => $this->generateVariable($expression),
            Input::class => $this->generateInput($expression),
            default => throw new FlowchartException('Statement não suportado: '.$expression::class)
        };
    }

    private function generateLiteral(Literal $literal): string
    {
        if ($literal->type === 'str') {
            return "\"{$literal->value}\"";
        }

        if ($literal->type === 'bool') {
            return $literal->value ? 'True' : 'False';
        }

        return (string) $literal->value;
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

    private function generateProcess(Process $process): string
    {
        $var = $process->variable->name;
        $expression = $process->expression;

        return "{$var} = {$expression}";
    }

    private function generateIf(IfStatement $if, int $level = 0): string
    {
        $indent = str_repeat('    ', $level);
        $innerIndent = str_repeat('    ', $level + 1);

        $lines = [];
        $lines[] = "{$indent}if {$if->condition}:";

        $thenLines = [];
        foreach ($if->then as $statement) {
            $stmtCode = $this->generateStatement($statement, $level + 1);
            if ($stmtCode !== '') {
                $thenLines[] = $stmtCode;
            }
        }
        if (empty($thenLines)) {
            $lines[] = "{$innerIndent}pass";
        } else {
            $lines = array_merge($lines, $thenLines);
        }

        if (! empty($if->else)) {
            $lines[] = "{$indent}else:";
            $elseLines = [];
            foreach ($if->else as $statement) {
                $stmtCode = $this->generateStatement($statement, $level + 1);
                if ($stmtCode !== '') {
                    $elseLines[] = $stmtCode;
                }
            }
            if (empty($elseLines)) {
                $lines[] = "{$innerIndent}pass";
            } else {
                $lines = array_merge($lines, $elseLines);
            }
        }

        return implode(PHP_EOL, $lines);
    }
}

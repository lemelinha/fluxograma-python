<?php

namespace App\Ast;

interface Statement {}
interface Expression {}

final readonly class Ast 
{
    public function __construct(public array $statements) {}
}

final readonly class InitStatement implements Statement {}

final readonly class EndStatement implements Statement {}

final readonly class InputExpression implements Expression 
{
    public function __construct(
        public string $type,
        public Expression $expression
    ) {}
}

final readonly class OutputStatement implements Statement 
{
    public function __construct(
        public Expression $value
    ) {}
}

final readonly class AssignmentStatement implements Statement 
{
    public function __construct(
        public string $varName,
        public Expression $expression
    ) {}
}

final readonly class BinaryExpression implements Expression 
{
    public function __construct(
        public string $operator,
        public Expression $left,
        public Expression $right
    ) {}
}

final readonly class Variable implements Expression 
{
    public function __construct(
        public string $value
    ) {}

    public function __toString()
    {
        return $this->value;
    }
}

final readonly class Literal implements Expression 
{
    public function __construct(
        public string $value
    ) {}

    public function __toString()
    {
        return $this->value;
    }
}

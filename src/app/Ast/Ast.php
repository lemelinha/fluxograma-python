<?php

namespace App\Ast;

abstract readonly class Node {}

interface Statement {}
interface Expression {}

final readonly class Ast {
    public function __construct(public array $statements) {}
}

final readonly class InitStatement extends Node implements Statement {}

final readonly class EndStatement extends Node implements Statement {}

final readonly class InputStatement extends Node implements Expression {
    public function __construct(
        public string $type,
        public Literal|Variable $expression
    ) {}
}

final readonly class OutputStatement extends Node implements Statement {
    public function __construct(
        public Variable|Literal $value
    ) {}
}

final readonly class AssignmentStatement extends Node implements Statement {
    public function __construct(
        public string $varName,
        public Expression $expression
    ) {}
}

final readonly class BinaryExpression implements Expression {
    public function __construct(
        public string $operator,
        public Variable|Literal $left,
        public Variable|Literal $right
    ) {}
}

final readonly class Variable implements Expression {
    public function __construct(
        public string $varName
    ) {}
}

final readonly class Literal implements Expression {
    public function __construct(
        public string $type,
        public string $value
    ) {}
}

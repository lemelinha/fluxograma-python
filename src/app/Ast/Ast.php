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

final readonly class InputExpression extends Node implements Expression {
    public function __construct(
        public string $type,
        public Expression $expression
    ) {}
}

final readonly class OutputStatement extends Node implements Statement {
    public function __construct(
        public Expression $value
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
        public Expression $left,
        public Expression $right
    ) {}
}

final readonly class Variable implements Expression {
    public function __construct(
        public string $value
    ) {}

    public function __toString()
    {
        $value = $this->value;
        return $value;
    }
}

final readonly class Literal implements Expression {
    public function __construct(
        public string $value
    ) {}

    public function __toString()
    {
        $value = $this->value;
        return $value;
    }
}

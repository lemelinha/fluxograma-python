<?php

namespace App\Ast;

abstract readonly class Node {}

interface Statement {}
interface Expression {}

final readonly class Ast {
    public function __construct(public array $statements) {}
}

final readonly class InitStatement extends Node implements Statement {
    public function __construct(public array $init) {}
}

final readonly class EndStatement extends Node implements Statement {
    public function __construct(public array $end) {}
}

final readonly class InputStatement extends Node implements Statement {
    public function __construct(public array $input) {}
}

final readonly class OutputStatement extends Node implements Statement {
    public function __construct(public array $output) {}
}

final readonly class Variable implements Expression {
    public function __construct(public array $var) {}
}

final readonly class Literal implements Expression {
    public function __construct(public array $literal) {}
}

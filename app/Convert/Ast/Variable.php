<?php

namespace App\Convert\Ast;

class Variable implements Statement {
    public function __construct(
        public string $name
    ) {}
}

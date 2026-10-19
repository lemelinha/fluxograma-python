<?php

namespace App\Convert\Ast;

class Process implements Statement
{
    public function __construct(
        public Variable $variable,
        public string $expression
    ) {}
}

<?php

namespace App\Convert\Ast;

class Assignment implements Statement
{
    public function __construct(
        public Variable $variable,
        public Variable|Literal|Input $expression
    ) {}
}

<?php

namespace App\Convert\Ast;

class Output implements Statement {
    public function __construct(
        public Literal|Variable $expression
    ){}
}

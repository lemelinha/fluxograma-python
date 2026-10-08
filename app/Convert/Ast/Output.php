<?php

namespace App\Convert\Ast;

class Output {
    public function __construct(
        public string|Variable $expression
    ){}
}

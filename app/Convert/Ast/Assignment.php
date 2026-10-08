<?php

namespace App\Convert\Ast;

class Assignment {
    public function __construct(
        public Variable $var,
        public Variable|Literal|Input $expression
    ){}
}

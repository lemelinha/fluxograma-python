<?php

namespace App\Convert\Ast;

class Assignment {
    public function __construct(
        Variable $var,
        string|Input $expression
    ){}
}

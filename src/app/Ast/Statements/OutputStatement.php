<?php

namespace App\Ast\Statements;
use App\Ast\Expressions\Expression;

final readonly class OutputStatement implements Statement 
{
    public function __construct(
        public Expression $value
    ) {}
}

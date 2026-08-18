<?php

namespace App\Ast\Statements;
use App\Ast\Expressions\Expression;

final readonly class AssignmentStatement implements Statement 
{
    public function __construct(
        public string $varName,
        public Expression $expression
    ) {}
}

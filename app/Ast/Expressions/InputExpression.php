<?php

namespace App\Ast\Expressions;

final readonly class InputExpression implements Expression 
{
    public function __construct(
        public string $type,
        public Expression $expression
    ) {}
}

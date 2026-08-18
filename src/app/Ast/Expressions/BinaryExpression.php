<?php

namespace App\Ast\Expressions;

final readonly class BinaryExpression implements Expression 
{
    public function __construct(
        public string $operator,
        public Expression $left,
        public Expression $right
    ) {}
}

<?php

namespace App\Ast\Expressions;

final readonly class Variable implements Expression 
{
    public function __construct(
        public string $value
    ) {}

    public function __toString()
    {
        return $this->value;
    }
}

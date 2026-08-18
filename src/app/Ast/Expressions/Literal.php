<?php

namespace App\Ast\Expressions;

final readonly class Literal implements Expression 
{
    public function __construct(
        public string $value
    ) {}

    public function __toString()
    {
        return $this->value;
    }
}

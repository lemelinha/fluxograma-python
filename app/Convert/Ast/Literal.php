<?php

namespace App\Convert\Ast;

class Literal {
    public function __construct(
        string $type,
        string|int|float|bool $value
    ) {}
}

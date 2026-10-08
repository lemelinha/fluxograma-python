<?php

namespace App\Convert\Ast;

class Literal {
    public function __construct(
        public string $type,
        public string|int|float|bool $value
    ) {}
}

<?php

namespace App\Convert\Ast;

class Literal implements Statement {
    public function __construct(
        public string $type,
        public string|int|float|bool $value
    ) {}
}

<?php

namespace App\Convert\Ast;

class IfStatement implements Statement
{
    public function __construct(
        public string $condition,
        public array $then = [],
        public array $else = []
    ) {}
}

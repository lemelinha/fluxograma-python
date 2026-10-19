<?php

namespace App\Convert\Ast;

class Input implements Statement
{
    public function __construct(
        public string $type,
        public ?string $text = null
    ) {}
}

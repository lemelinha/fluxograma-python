<?php

namespace App\Convert\Ast;

class Input {
    public function __construct(
        public string $type,
        public string $text
    ){}
}

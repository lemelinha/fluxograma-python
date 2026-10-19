<?php

namespace App\Convert\Ast;

use App\Convert\CodeGenerator\PythonGenerator;

class Program
{
    public array $statements = [];

    public function toPython(): string
    {
        return (new PythonGenerator)->generate($this);
    }
}

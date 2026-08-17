<?php

namespace App\CodeGenerator\Python;

use App\Ast\Ast;

class AstToPython {
    public function convert(Ast $ast) {
        $code = [];
        $statements = $ast->statements;

        foreach ($statements as $statement) {
            echo $statement;
        }
    }
}

<?php

namespace App\Services;

use App\Ast\Ast;
use App\Flowchart\FlowchartGraph;

class FlowchartToAstService {
    public function convert(FlowchartGraph $graph): Ast{
        return new Ast(['ok']);
    }
}

<?php

namespace App\Services;

use App\Ast\Ast;
use App\Flowchart\FlowchartGraph;

class FlowchartToAstService {
    public function convert(FlowchartGraph $graph): Ast{
        $initNode = collect($graph->nodes)->filter(fn(array $n)=>$n["type"]=="init");
        $initId = $initNode->first()["id"];

        return new Ast([$initId]);
    }

    protected function walkGraph($graph, $from) {

    }
}

<?php

namespace App\Services;

class FlowchartToAstService {
    public function convert(array $nodes, array $edges){
        return ['nodes' => $nodes, "edges" => $edges];
    }
}

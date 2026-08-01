<?php

namespace App\Services;

class FlowchartToAstService {
    public function convert(string $source){
        return response()->json(['source' => 'Olá Mundo! '.$source]);
    }
}

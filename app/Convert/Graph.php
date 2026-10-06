<?php

namespace App\Convert;

class Graph {
    /**
     * @param array $nodes
     *                pattern:
     *                  [
     *                   [
     *                    'id', 'data', 'type', 'targets'
     *                   ],
     *                   ...
     *                  ]
     *                'targets' é um array dos ids dos nodes de saída
     */
    public array $nodes = [];

    public function validate(): bool {

    }
}

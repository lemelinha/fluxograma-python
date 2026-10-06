<?php

namespace App\Convert;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class FlowchartGraph {
    /**
     * Classe que convert $nodes e $edges para grafo
     */
    use ApiResponse;

    public function toGraph(array $nodes, array $edges): Graph|JsonResponse {
        $graph = new Graph();    

        foreach ($nodes as $node) {
            if (! array_key_exists('id', $node)) {
                return $this->errorResponse(
                    'Node do fluxograma sem ID',
                    400,
                    code: 'NODE_WITHOUT_ID'
                );
            }

            $graph->nodes[$node['id']] = $node;
        }

        foreach ($edges as $edge) {
            if (
                ! array_key_exists('source', $edge) ||
                ! array_key_exists('target', $edge)
            ) {
                return $this->errorResponse(
                    'Edge mal formado',
                    400,
                    code: 'EDGE_MALFORMED'
                );
            }

            $source = $edge['source'];
            $target = $edge['target'];

            if (! array_key_exists($source, $graph->nodes)) {
                return $this->errorResponse(
                    'Aresta com id de node de saída inválido',
                    400,
                    code: 'INVALID_EDGE_SOURCE_ID'
                );
            }

            $graph->nodes[$source]['targets'][] = $target;
        }

        return $graph;
    }
}

<?php

namespace App\Convert;

use App\Exceptions\FlowchartException;

class FlowchartGraph {
    /**
     * Classe que convert $nodes e $edges para grafo
     */

    public function toGraph(array $nodes, array $edges): Graph {
        $graph = new Graph();    

        foreach ($nodes as $node) {
            if (! array_key_exists('id', $node)) {
                throw new FlowchartException('Node sem ID');
            }
            if (array_key_exists($node['id'], $graph->nodes)) {
                throw new FlowchartException('Node com ID duplicado');
            }

            $graph->nodes[$node['id']] = $node;
        }

        foreach ($edges as $edge) {
            if (
                ! array_key_exists('source', $edge) ||
                ! array_key_exists('target', $edge)
            ) {
                throw new FlowchartException('Edge mal formado');;
            }

            $source = $edge['source'];
            $target = $edge['target'];

            if (! array_key_exists($source, $graph->nodes)) {
                throw new FlowchartException('Aresta com id de node de fonte inválido');
            }

            if (! array_key_exists($target, $graph->nodes)) {
                throw new FlowchartException('Aresta com id de node de destino inválido');
            }

            $graph->nodes[$source]['targets'][] = $target;
        }

        return $graph;
    }
}

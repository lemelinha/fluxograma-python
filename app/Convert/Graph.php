<?php

namespace App\Convert;
use App\Exceptions\FlowchartException;

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
    public ?int $startNodeId = null;

    public function validate(): void {
        // verifica se existe apenas 1 node de inicio
        $startId = collect($this->nodes)->filter(fn (array $n) => $n['type'] == 'start');
        if ( count($startId) != 1 ) {
            throw new FlowchartException('Quantidade de Nodes ID inválidos');
        }
        $this->startNodeId = $startId->first()['id'];

        // verificar se o node de inicio tem aresta de saída
        if (! array_key_exists('targets', $this->nodes[$this->startNodeId]) ) {
            throw new FlowchartException('Node de início deve conter arestas de saída');
        }

        // Node de inicio apenas com 1 aresta de saída
        if ( count($this->nodes[$this->startNodeId]['targets']) > 1 ) {
            throw new FlowchartException('Node de início deve conter apenas 1 aresta de saída');
        }
            
        foreach($this->nodes as $node) {
            // verificando que nodes devem obrigatoriamente ter no minimo 1 aresta de saida, exceto node end
            if (array_key_exists('targets', $node) && count($node['targets']) < 1 && $node['type'] != 'end') {
                throw new FlowchartException('Node sem aresta de saída');
            }

            
        }
    }
}

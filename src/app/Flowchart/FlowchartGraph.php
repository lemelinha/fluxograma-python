<?php

namespace App\Flowchart;

use App\Exceptions\FlowchartGraphException;

final class FlowchartGraph
{
    protected array $nodes = [];

    protected array $inComing = [];

    protected array $outGoing = [];

    public function __construct(array $nodes, array $edges)
    {
        // criar indeces do grafo
        foreach ($nodes as $node) {
            if (!isset($node['id'], $node['type'])) {
                throw new FlowchartGraphException('Node deve ter um ID e um TYPE');
            }
            if (array_key_exists($node['id'], $this->nodes)) {
                throw new FlowchartGraphException('Nodes com IDs duplicados');
            }
            $this->nodes[$node['id']] = $node;
        }

        // criar as arestas direcionadas
        foreach ($edges as $edge) {
            if (!isset($edge['source'], $edge['target'])) {
                throw new FlowchartGraphException('Edge deve ter SOURCE e TARGET');
            }
            $this->inComing[$edge['target']][] = [
                'source' => $edge['source'],
            ];

            $this->outGoing[$edge['source']][] = [
                'target' => $edge['target'],
                'label' => $edge['label'] ?? null,
            ];
        }
    }

    // validar fluxos lógicos do fluxograma
    public function validate(): void
    {
        $supportedTypes = ['init', 'end', 'input', 'output', 'operation'];

        foreach ($this->nodes as $node) {
            if (! in_array($node['type'], $supportedTypes, true)) {
                throw new FlowchartGraphException("TYPE não suportado: {$node['type']}");
            }
        }

        foreach (array_keys($this->inComing) as $target) {
            if (!isset($this->nodes[$target])) {
                throw new FlowchartGraphException("Referência de TARGET desconhecida: $target");
            }
        }

        foreach (array_keys($this->outGoing) as $source) {
            if (! isset($this->nodes[$source])) {
                throw new FlowchartGraphException("Referência de SOURCE desconhecida: $source");
            }
        }

        // init node
        $init = collect($this->nodes)->filter(
            fn (array $node) => $node['type'] == 'init'
        );
        // ver se é unico
        if ($init->count() != 1) {
            throw new FlowchartGraphException('Exatamente um InitNode esperado');
        }
        // ver se tem uma saida e sem entrada
        $initId = $init->first()['id'];
        if (
            array_key_exists($initId, $this->inComing) ||
            !array_key_exists($initId, $this->outGoing) ||
            count($this->outGoing[$initId]) != 1
        ) {
            throw new FlowchartGraphException('Init node deve ter uma aresta de saída e nenhuma de entrada');
        }

        // end node
        $end = collect($this->nodes)->filter(
            fn (array $node) => $node['type'] == 'end'
        );
        $endId = $end->first()['id'];
        if (
            array_key_exists($endId, $this->outGoing) ||
            !array_key_exists($endId, $this->inComing)
        ) {
            throw new FlowchartGraphException('End node deve ter uma ou mais arestas de entrada e nenhuma de saída');
        }
    }

    public function __get(string $name): array
    {
        if (! property_exists($this, $name)) {
            throw new FlowchartGraphException("Propriedade $name não existe");
        }

        return $this->$name;
    }
}

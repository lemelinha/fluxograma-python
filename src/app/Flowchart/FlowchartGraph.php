<?php

namespace App\Flowchart;

use App\Exceptions\FlowchartGraphException;

final class FlowchartGraph {
    protected array $nodes = [];
    protected array $inComing = [];
    protected array $outGoing = [];

    public function __construct(array $nodes, array $edges){
        // criar indeces do grafo
        foreach ($nodes as $node)  {
            if (array_key_exists($node["id"], $this->nodes)) {
                throw new FlowchartGraphException("Nodes with duplicate IDs");
            }
            $this->nodes[$node["id"]] = $node;
        }

        // criar as arestas direcionadas
        foreach ($edges as $edge) {
            $this->inComing[$edge["target"]][] = [
                "source" => $edge["source"]
            ];

            $this->outGoing[$edge["source"]][] = [
                "target" => $edge["target"],
                "label" => $edge["label"]??null
            ];
        }
    }

    // validar fluxos lógicos do fluxograma
    public function validate() {
        // init node
        $init = collect($this->nodes)->filter(
            fn(array $node) => $node["type"] == "init"
        );
        // ver se é unico
        if ($init->count() != 1) {
            throw new FlowchartGraphException("Exatly one init node expected");
        }
        // ver se tem uma saida e sem entrada
        $initId = $init->first()["id"];
        if (
            array_key_exists($initId, $this->inComing) ||
            !array_key_exists($initId, $this->outGoing) ||
            count($this->outGoing[$initId]) != 1
        ) {
            throw new FlowchartGraphException("Init node must have one out edge and no one entry edge");
        }

        // end node
        $end = collect($this->nodes)->filter(
            fn(array $node) => $node["type"] == 'end'
        );
        if ($end->count() < 1) {
            throw new FlowchartGraphException("More than one end node expected");
        }
        $endId = $end->first()['id'];
        if (
            array_key_exists($endId, $this->outGoing) ||
            !array_key_exists($endId, $this->inComing) ||
            count($this->inComing[$endId]) < 1
        ) {
            throw new FlowchartGraphException("End node must have one or more entry edge and no one out edge");
        }
    }

    public function __get(string $name): array
    {
        if (!property_exists($this, $name)) {
            throw new FlowchartGraphException("Property $name does nor exist");
        }
        return $this->$name;
    }
}

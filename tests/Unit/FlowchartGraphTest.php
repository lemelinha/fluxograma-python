<?php

use App\Convert\FlowchartGraph;
use App\Convert\Graph;
use App\Exceptions\FlowchartException;

test('converte nodes e edges em um grafo com sucesso', function () {
    $nodes = [
        ['id' => '1', 'type' => 'start'],
        ['id' => '2', 'type' => 'end'],
    ];
    $edges = [
        ['source' => '1', 'target' => '2'],
    ];

    $converter = new FlowchartGraph;
    $graph = $converter->toGraph($nodes, $edges);

    expect($graph)->toBeInstanceOf(Graph::class)
        ->and($graph->nodes)->toHaveCount(2)
        ->and($graph->nodes['1']['targets'])->toBe(['2']);
});

test('lança exceção se algum node não tiver ID', function () {
    $nodes = [
        ['type' => 'start'],
    ];
    $edges = [];

    $converter = new FlowchartGraph;
    $converter->toGraph($nodes, $edges);
})->throws(FlowchartException::class, 'Node sem ID');

test('lança exceção se houver IDs de nodes duplicados', function () {
    $nodes = [
        ['id' => '1', 'type' => 'start'],
        ['id' => '1', 'type' => 'end'],
    ];
    $edges = [];

    $converter = new FlowchartGraph;
    $converter->toGraph($nodes, $edges);
})->throws(FlowchartException::class, 'Node com ID duplicado');

test('lança exceção se edge não tiver source ou target', function (array $edge) {
    $nodes = [
        ['id' => '1', 'type' => 'start'],
        ['id' => '2', 'type' => 'end'],
    ];

    $converter = new FlowchartGraph;
    $converter->toGraph($nodes, [$edge]);
})->with([
    'sem target' => [['source' => '1']],
    'sem source' => [['target' => '2']],
])->throws(FlowchartException::class, 'Edge mal formado');

test('lança exceção se source da aresta apontar para node inexistente', function () {
    $nodes = [
        ['id' => '2', 'type' => 'end'],
    ];
    $edges = [
        ['source' => '999', 'target' => '2'],
    ];

    $converter = new FlowchartGraph;
    $converter->toGraph($nodes, $edges);
})->throws(FlowchartException::class, 'Aresta com id de node de fonte inválido');

test('lança exceção se target da aresta apontar para node inexistente', function () {
    $nodes = [
        ['id' => '1', 'type' => 'start'],
    ];
    $edges = [
        ['source' => '1', 'target' => '999'],
    ];

    $converter = new FlowchartGraph;
    $converter->toGraph($nodes, $edges);
})->throws(FlowchartException::class, 'Aresta com id de node de destino inválido');

test('armazena labels de branches nas saídas do nó de decisão', function () {
    $nodes = [
        ['id' => 'dec', 'type' => 'decision'],
        ['id' => 'target_sim', 'type' => 'output'],
        ['id' => 'target_nao', 'type' => 'output'],
    ];
    $edges = [
        ['source' => 'dec', 'target' => 'target_sim', 'label' => 'sim'],
        ['source' => 'dec', 'target' => 'target_nao', 'label' => 'nao'],
    ];

    $converter = new FlowchartGraph;
    $graph = $converter->toGraph($nodes, $edges);

    expect($graph->nodes['dec']['branches'])->toBe([
        'sim' => 'target_sim',
        'nao' => 'target_nao',
    ]);
});

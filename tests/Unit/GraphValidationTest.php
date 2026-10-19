<?php

use App\Convert\Graph;
use App\Exceptions\FlowchartException;

test('valida com sucesso um grafo linear válido', function () {
    $graph = new Graph;
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => ['id' => '2', 'type' => 'input', 'targets' => ['3']],
        '3' => ['id' => '3', 'type' => 'end'],
    ];

    expect(fn () => $graph->validate())->not->toThrow(FlowchartException::class);
    expect($graph->startNodeId)->toBe('1');
});

test('valida com sucesso um grafo com decisão e múltiplos fins', function () {
    $graph = new Graph;
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => [
            'id' => '2',
            'type' => 'decision',
            'data' => ['expression' => 'x > 0'],
            'targets' => ['3', '4'],
            'branches' => ['sim' => '3', 'nao' => '4'],
        ],
        '3' => ['id' => '3', 'type' => 'end'],
        '4' => ['id' => '4', 'type' => 'end'],
    ];

    expect(fn () => $graph->validate())->not->toThrow(FlowchartException::class);
});

test('lança exceção se houver quantidade inválida de nós Start', function (array $nodes) {
    $graph = new Graph;
    $graph->nodes = $nodes;

    $graph->validate();
})->with([
    'nenhum start' => [[
        '1' => ['id' => '1', 'type' => 'end'],
    ]],
    'dois starts' => [[
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['3']],
        '2' => ['id' => '2', 'type' => 'start', 'targets' => ['3']],
        '3' => ['id' => '3', 'type' => 'end'],
    ]],
])->throws(FlowchartException::class, 'Quantidade de Nodes Start inválidos');

test('lança exceção se o nó Start não tiver aresta de saída', function () {
    $graph = new Graph;
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start'],
        '2' => ['id' => '2', 'type' => 'end'],
    ];

    $graph->validate();
})->throws(FlowchartException::class, 'Node de início deve conter aresta de saída');

test('lança exceção se o nó Start tiver mais de uma aresta de saída', function () {
    $graph = new Graph;
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2', '3']],
        '2' => ['id' => '2', 'type' => 'end'],
        '3' => ['id' => '3', 'type' => 'end'],
    ];

    $graph->validate();
})->throws(FlowchartException::class, 'Node de início deve conter apenas 1 aresta de saída');

test('lança exceção se não houver pelo menos um nó de fim', function () {
    $graph = new Graph;
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => ['id' => '2', 'type' => 'input', 'targets' => ['1']],
    ];

    $graph->validate();
})->throws(FlowchartException::class, 'Pelo menos 1 Node de End deve existir');

test('lança exceção se nó não tiver tipo definido', function () {
    $graph = new Graph;
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => ['id' => '2', 'targets' => ['3']],
        '3' => ['id' => '3', 'type' => 'end'],
    ];

    $graph->validate();
})->throws(FlowchartException::class, 'Node sem tipo definido');

test('lança exceção se tipo de nó não for permitido', function () {
    $graph = new Graph;
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => ['id' => '2', 'type' => 'unsupported_type', 'targets' => ['3']],
        '3' => ['id' => '3', 'type' => 'end'],
    ];

    $graph->validate();
})->throws(FlowchartException::class, 'Tipo de Node inválido');

test('lança exceção se nó End tiver arestas de saída', function () {
    $graph = new Graph;
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => ['id' => '2', 'type' => 'end', 'targets' => ['1']],
    ];

    $graph->validate();
})->throws(FlowchartException::class, 'Node end não deve ter arestas de saída');

test('lança exceção se nó intermediário não tiver arestas de saída', function () {
    $graph = new Graph;
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => ['id' => '2', 'type' => 'input'],
        '3' => ['id' => '3', 'type' => 'end'],
    ];

    $graph->validate();
})->throws(FlowchartException::class, 'Node deve ter saídas');

test('lança exceção se nó não-decisão tiver mais de uma aresta de saída', function () {
    $graph = new Graph;
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => ['id' => '2', 'type' => 'input', 'targets' => ['3', '4']],
        '3' => ['id' => '3', 'type' => 'end'],
        '4' => ['id' => '4', 'type' => 'end'],
    ];

    $graph->validate();
})->throws(FlowchartException::class, 'Nodes devem conter apenas uma aresta de saída');

test('lança exceção se nó de decisão não tiver expressão', function () {
    $graph = new Graph;
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => [
            'id' => '2',
            'type' => 'decision',
            'data' => [],
            'targets' => ['3', '4'],
            'branches' => ['sim' => '3', 'nao' => '4'],
        ],
        '3' => ['id' => '3', 'type' => 'end'],
        '4' => ['id' => '4', 'type' => 'end'],
    ];

    $graph->validate();
})->throws(FlowchartException::class, 'Node de decisão sem expressão');

test('lança exceção se nó de decisão não tiver exatamente 2 saídas', function () {
    $graph = new Graph;
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => [
            'id' => '2',
            'type' => 'decision',
            'data' => ['expression' => 'x > 0'],
            'targets' => ['3'],
            'branches' => ['sim' => '3'],
        ],
        '3' => ['id' => '3', 'type' => 'end'],
    ];

    $graph->validate();
})->throws(FlowchartException::class, 'Nó de decisão deve conter exatamente 2 saídas');

test('lança exceção se nó de decisão não tiver branches sim e nao', function () {
    $graph = new Graph;
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => [
            'id' => '2',
            'type' => 'decision',
            'data' => ['expression' => 'x > 0'],
            'targets' => ['3', '4'],
            'branches' => ['sim' => '3', 'talvez' => '4'],
        ],
        '3' => ['id' => '3', 'type' => 'end'],
        '4' => ['id' => '4', 'type' => 'end'],
    ];

    $graph->validate();
})->throws(FlowchartException::class, 'Nó de decisão precisa ter saídas com labels "sim" e "nao"');

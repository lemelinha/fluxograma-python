<?php

test('converte fluxograma linear com sucesso via API POST', function () {
    $payload = [
        'nodes' => [
            ['id' => '1', 'type' => 'start'],
            ['id' => '2', 'type' => 'input', 'data' => ['variable' => 'nome', 'dataType' => 'str', 'label' => 'Nome']],
            ['id' => '3', 'type' => 'output', 'data' => ['expression' => 'nome']],
            ['id' => '4', 'type' => 'end'],
        ],
        'edges' => [
            ['source' => '1', 'target' => '2'],
            ['source' => '2', 'target' => '3'],
            ['source' => '3', 'target' => '4'],
        ],
    ];

    $response = $this->postJson('/api/convert/flowchart/to/python', $payload);

    $response->assertSuccessful()
        ->assertJson([
            'status' => 'success',
            'message' => 'tudo ok!!!',
        ]);

    $code = $response->json('data.code');
    expect($code)->toContain("nome = str(input('Nome '))")
        ->and($code)->toContain('print(nome)');
});

test('converte fluxograma com if-else e múltiplos nós end', function () {
    $payload = [
        'nodes' => [
            ['id' => '1', 'type' => 'start'],
            ['id' => '2', 'type' => 'input', 'data' => ['variable' => 'x', 'dataType' => 'int', 'label' => 'X']],
            ['id' => '3', 'type' => 'decision', 'data' => ['expression' => 'x > 0']],
            ['id' => '4', 'type' => 'output', 'data' => ['expression' => 'Positivo']],
            ['id' => '5', 'type' => 'output', 'data' => ['expression' => 'Nao Positivo']],
            ['id' => 'end1', 'type' => 'end'],
            ['id' => 'end2', 'type' => 'end'],
        ],
        'edges' => [
            ['source' => '1', 'target' => '2'],
            ['source' => '2', 'target' => '3'],
            ['source' => '3', 'target' => '4', 'label' => 'sim'],
            ['source' => '3', 'target' => '5', 'label' => 'nao'],
            ['source' => '4', 'target' => 'end1'],
            ['source' => '5', 'target' => 'end2'],
        ],
    ];

    $response = $this->postJson('/api/convert/flowchart/to/python', $payload);

    $response->assertSuccessful();

    $code = $response->json('data.code');
    expect($code)->toContain('if x > 0:')
        ->and($code)->toContain('    print(Positivo)')
        ->and($code)->toContain('else:')
        ->and($code)->toContain('    print(Nao Positivo)');
});

test('retorna erro 400 se linguagem de origem for inválida', function () {
    $response = $this->postJson('/api/convert/invalid_lang/to/python', [
        'nodes' => [],
        'edges' => [],
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'status' => 'error',
            'code' => 'INVALID_SOURCE_LANGUANGE',
            'message' => 'Linguagem de entrada inválida',
        ]);
});

test('retorna erro 400 se linguagem de destino for inválida', function () {
    $response = $this->postJson('/api/convert/flowchart/to/unsupported_target', [
        'nodes' => [],
        'edges' => [],
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'status' => 'error',
            'code' => 'INVALID_TARGET_LANGUANGE',
            'message' => 'Linguagem de saída inválida',
        ]);
});

test('retorna erro 422 se nodes ou edges estiverem ausentes', function (array $payload) {
    $response = $this->postJson('/api/convert/flowchart/to/python', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(array_keys($payload === [] ? ['nodes' => '', 'edges' => ''] : array_diff_key(['nodes' => '', 'edges' => ''], $payload)));
})->with([
    'sem nada' => [[]],
    'sem nodes' => [['edges' => []]],
    'sem edges' => [['nodes' => []]],
]);

test('retorna erro 400 do envelope ApiResponse se o grafo for estruturalmente inválido', function (array $payload, string $expectedMessage) {
    $response = $this->postJson('/api/convert/flowchart/to/python', $payload);

    $response->assertStatus(400)
        ->assertJson([
            'status' => 'error',
            'message' => $expectedMessage,
        ]);
})->with([
    'sem start' => [
        [
            'nodes' => [
                ['id' => '1', 'type' => 'input', 'data' => ['variable' => 'x', 'dataType' => 'int']],
                ['id' => '2', 'type' => 'end'],
            ],
            'edges' => [
                ['source' => '1', 'target' => '2'],
            ],
        ],
        'Quantidade de Nodes Start inválidos',
    ],
    'node duplicado' => [
        [
            'nodes' => [
                ['id' => '1', 'type' => 'start'],
                ['id' => '1', 'type' => 'end'],
            ],
            'edges' => [
                ['source' => '1', 'target' => '1'],
            ],
        ],
        'Node com ID duplicado',
    ],
    'aresta para nó inexistente' => [
        [
            'nodes' => [
                ['id' => '1', 'type' => 'start'],
                ['id' => '2', 'type' => 'end'],
            ],
            'edges' => [
                ['source' => '1', 'target' => '999'],
            ],
        ],
        'Aresta com id de node de destino inválido',
    ],
]);

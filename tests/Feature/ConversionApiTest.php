<?php

function convertUri(string $path, array $data): string
{
    return "$path?".http_build_query($data);
}

it('converte fluxograma em Python via API', function () {
    $data = loadFixture('07-sum-two-numbers');
    $result = $this->post('/flowchart/to/python', $data);
    
    $result->assertOk()
        ->assertJsonPath('status', 'ok');
    $result['code']->toBe($data['code']);
});

it('converte operações complexas via API', function () {
    $this->getJson(convertUri('/flowchart/to/python', loadFixture('11-complex-operations')))
        ->assertOk()
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('code.3', 'total = ((a + b) + c)')
        ->assertJsonPath('code.4', 'resultado = ((a + 2) * b)');
});

it('rejeita source igual a target', function () {
    $this->get(convertUri('/flowchart/to/flowchart', loadFixture('07-sum-two-numbers')))
        ->assertStatus(400)
        ->assertJsonPath('status', 'error');
});

it('rejeita conversão python para outro formato ainda não implementada', function () {
    $this->get(convertUri('/python/to/flowchart', loadFixture('07-sum-two-numbers')))
        ->assertStatus(400)
        ->assertJsonPath('status', 'error');
});

it('rejeita grafo inválido com 422', function () {
    $data = loadFixture('07-sum-two-numbers');

    $data['nodes'][0]['type'] = 'decisao';

    $this->get(convertUri('/flowchart/to/python', $data))
        ->assertStatus(422)
        ->assertJsonPath('status', 'error');
});

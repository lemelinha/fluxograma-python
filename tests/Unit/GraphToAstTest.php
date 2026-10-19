<?php

use App\Convert\Ast\Assignment;
use App\Convert\Ast\End;
use App\Convert\Ast\IfStatement;
use App\Convert\Ast\Input;
use App\Convert\Ast\Output;
use App\Convert\Ast\Process;
use App\Convert\Ast\Program;
use App\Convert\Ast\Start;
use App\Convert\Graph;
use App\Exceptions\FlowchartException;

test('converte fluxo linear completo para AST', function () {
    $graph = new Graph;
    $graph->startNodeId = '1';
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => [
            'id' => '2',
            'type' => 'input',
            'data' => ['variable' => 'idade', 'dataType' => 'int', 'label' => 'Idade'],
            'targets' => ['3'],
        ],
        '3' => [
            'id' => '3',
            'type' => 'process',
            'data' => ['variable' => 'dobro', 'expression' => 'idade * 2'],
            'targets' => ['4'],
        ],
        '4' => [
            'id' => '4',
            'type' => 'output',
            'data' => ['expression' => 'dobro'],
            'targets' => ['5'],
        ],
        '5' => ['id' => '5', 'type' => 'end'],
    ];

    $ast = $graph->toAst();

    expect($ast)->toBeInstanceOf(Program::class)
        ->and($ast->statements)->toHaveCount(5)
        ->and($ast->statements[0])->toBeInstanceOf(Start::class)
        ->and($ast->statements[1])->toBeInstanceOf(Assignment::class)
        ->and($ast->statements[1]->variable->name)->toBe('idade')
        ->and($ast->statements[1]->expression)->toBeInstanceOf(Input::class)
        ->and($ast->statements[2])->toBeInstanceOf(Process::class)
        ->and($ast->statements[2]->variable->name)->toBe('dobro')
        ->and($ast->statements[2]->expression)->toBe('idade * 2')
        ->and($ast->statements[3])->toBeInstanceOf(Output::class)
        ->and($ast->statements[3]->expression)->toBe('dobro')
        ->and($ast->statements[4])->toBeInstanceOf(End::class);
});

test('lança exceção se input tiver data mal formada', function (array $data) {
    $graph = new Graph;
    $graph->startNodeId = '1';
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => ['id' => '2', 'type' => 'input', 'data' => $data, 'targets' => ['3']],
        '3' => ['id' => '3', 'type' => 'end'],
    ];

    $graph->toAst();
})->with([
    'sem variable' => [['dataType' => 'str']],
    'sem dataType' => [['variable' => 'x']],
    'vazio' => [[]],
])->throws(FlowchartException::class, 'Input Data mal formado');

test('lança exceção se nome da variável no input for inválido', function (string $varName) {
    $graph = new Graph;
    $graph->startNodeId = '1';
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => ['id' => '2', 'type' => 'input', 'data' => ['variable' => $varName, 'dataType' => 'int'], 'targets' => ['3']],
        '3' => ['id' => '3', 'type' => 'end'],
    ];

    $graph->toAst();
})->with([
    'começa com numero' => ['1numero'],
    'com hifen' => ['minha-var'],
    'com espaço' => ['minha var'],
    'com caracteres especiais' => ['var$name'],
])->throws(FlowchartException::class, 'Nome de variável no Input inválido');

test('lança exceção se dataType do input não for permitido', function () {
    $graph = new Graph;
    $graph->startNodeId = '1';
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => ['id' => '2', 'type' => 'input', 'data' => ['variable' => 'x', 'dataType' => 'custom'], 'targets' => ['3']],
        '3' => ['id' => '3', 'type' => 'end'],
    ];

    $graph->toAst();
})->throws(FlowchartException::class, 'Tipo de input inválido');

test('lança exceção se node process não tiver dados necessários', function (array $data, string $message) {
    $graph = new Graph;
    $graph->startNodeId = '1';
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => ['id' => '2', 'type' => 'process', 'data' => $data, 'targets' => ['3']],
        '3' => ['id' => '3', 'type' => 'end'],
    ];

    $graph->toAst();
})->with([
    'sem variable' => [['expression' => 'a + b'], 'Node Process sem data'],
    'sem expression' => [['variable' => 'total'], 'Node Process sem expressão'],
])->throws(FlowchartException::class);

test('lança exceção se output não tiver expressão', function () {
    $graph = new Graph;
    $graph->startNodeId = '1';
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['2']],
        '2' => ['id' => '2', 'type' => 'output', 'data' => [], 'targets' => ['3']],
        '3' => ['id' => '3', 'type' => 'end'],
    ];

    $graph->toAst();
})->throws(FlowchartException::class, 'Node Output sem data');

test('converte decisão com dois caminhos para fins separados', function () {
    $graph = new Graph;
    $graph->startNodeId = '1';
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['dec']],
        'dec' => [
            'id' => 'dec',
            'type' => 'decision',
            'data' => ['expression' => 'nota >= 7'],
            'targets' => ['out_sim', 'out_nao'],
            'branches' => ['sim' => 'out_sim', 'nao' => 'out_nao'],
        ],
        'out_sim' => ['id' => 'out_sim', 'type' => 'output', 'data' => ['expression' => 'Aprovado'], 'targets' => ['end1']],
        'out_nao' => ['id' => 'out_nao', 'type' => 'output', 'data' => ['expression' => 'Reprovado'], 'targets' => ['end2']],
        'end1' => ['id' => 'end1', 'type' => 'end'],
        'end2' => ['id' => 'end2', 'type' => 'end'],
    ];

    $ast = $graph->toAst();

    expect($ast->statements)->toHaveCount(2)
        ->and($ast->statements[0])->toBeInstanceOf(Start::class)
        ->and($ast->statements[1])->toBeInstanceOf(IfStatement::class);

    /** @var IfStatement $ifStmt */
    $ifStmt = $ast->statements[1];
    expect($ifStmt->condition)->toBe('nota >= 7')
        ->and($ifStmt->then)->toHaveCount(2)
        ->and($ifStmt->then[0])->toBeInstanceOf(Output::class)
        ->and($ifStmt->then[0]->expression)->toBe('Aprovado')
        ->and($ifStmt->then[1])->toBeInstanceOf(End::class)
        ->and($ifStmt->else)->toHaveCount(2)
        ->and($ifStmt->else[0])->toBeInstanceOf(Output::class)
        ->and($ifStmt->else[0]->expression)->toBe('Reprovado')
        ->and($ifStmt->else[1])->toBeInstanceOf(End::class);
});

test('converte decisão onde os ramos convergem em um nó comum antes do fim', function () {
    $graph = new Graph;
    $graph->startNodeId = '1';
    $graph->nodes = [
        '1' => ['id' => '1', 'type' => 'start', 'targets' => ['dec']],
        'dec' => [
            'id' => 'dec',
            'type' => 'decision',
            'data' => ['expression' => 'pontos > 100'],
            'targets' => ['proc_sim', 'proc_nao'],
            'branches' => ['sim' => 'proc_sim', 'nao' => 'proc_nao'],
        ],
        'proc_sim' => ['id' => 'proc_sim', 'type' => 'process', 'data' => ['variable' => 'bonus', 'expression' => '50'], 'targets' => ['join_out']],
        'proc_nao' => ['id' => 'proc_nao', 'type' => 'process', 'data' => ['variable' => 'bonus', 'expression' => '0'], 'targets' => ['join_out']],
        'join_out' => ['id' => 'join_out', 'type' => 'output', 'data' => ['expression' => 'bonus'], 'targets' => ['end']],
        'end' => ['id' => 'end', 'type' => 'end'],
    ];

    $ast = $graph->toAst();

    expect($ast->statements)->toHaveCount(4)
        ->and($ast->statements[0])->toBeInstanceOf(Start::class)
        ->and($ast->statements[1])->toBeInstanceOf(IfStatement::class)
        ->and($ast->statements[2])->toBeInstanceOf(Output::class)
        ->and($ast->statements[2]->expression)->toBe('bonus')
        ->and($ast->statements[3])->toBeInstanceOf(End::class);

    /** @var IfStatement $ifStmt */
    $ifStmt = $ast->statements[1];
    expect($ifStmt->condition)->toBe('pontos > 100')
        ->and($ifStmt->then)->toHaveCount(1)
        ->and($ifStmt->then[0])->toBeInstanceOf(Process::class)
        ->and($ifStmt->else)->toHaveCount(1)
        ->and($ifStmt->else[0])->toBeInstanceOf(Process::class);
});

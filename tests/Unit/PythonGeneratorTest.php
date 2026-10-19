<?php

use App\Convert\Ast\Assignment;
use App\Convert\Ast\End;
use App\Convert\Ast\IfStatement;
use App\Convert\Ast\Input;
use App\Convert\Ast\Literal;
use App\Convert\Ast\Output;
use App\Convert\Ast\Process;
use App\Convert\Ast\Program;
use App\Convert\Ast\Start;
use App\Convert\Ast\Statement;
use App\Convert\Ast\Variable;
use App\Convert\CodeGenerator\PythonGenerator;
use App\Exceptions\FlowchartException;

test('gera código python para fluxo linear de input, process e output', function () {
    $program = new Program;
    $program->statements = [
        new Start,
        new Assignment(new Variable('a'), new Input('int', 'Digite a:')),
        new Assignment(new Variable('b'), new Input('int', 'Digite b:')),
        new Process(new Variable('soma'), 'a + b'),
        new Output('soma'),
        new End,
    ];

    $generator = new PythonGenerator;
    $code = $generator->generate($program);

    $expected = implode(PHP_EOL, [
        "a = int(input('Digite a: '))",
        "b = int(input('Digite b: '))",
        'soma = a + b',
        'print(soma)',
    ]).PHP_EOL;

    expect($code)->toBe($expected);
});

test('gera tipos de input corretos para str, int, float e bool', function (string $type, string $expectedCode) {
    $program = new Program;
    $program->statements = [
        new Assignment(new Variable('x'), new Input($type, 'Valor')),
    ];

    $generator = new PythonGenerator;
    $code = trim($generator->generate($program));

    expect($code)->toBe($expectedCode);
})->with([
    'str' => ['str', "x = str(input('Valor '))"],
    'int' => ['int', "x = int(input('Valor '))"],
    'float' => ['float', "x = float(input('Valor '))"],
    'bool' => ['bool', "x = bool(input('Valor '))"],
]);

test('gera atribuições com variáveis e literais de tipos diferentes', function (Statement $expr, string $expectedCode) {
    $program = new Program;
    $program->statements = [
        new Assignment(new Variable('x'), $expr),
    ];

    $generator = new PythonGenerator;
    $code = trim($generator->generate($program));

    expect($code)->toBe($expectedCode);
})->with([
    'variável' => [new Variable('y'), 'x = y'],
    'literal string' => [new Literal('str', 'ola mundo'), 'x = "ola mundo"'],
    'literal int' => [new Literal('int', 42), 'x = 42'],
    'literal float' => [new Literal('float', 3.14), 'x = 3.14'],
    'literal bool true' => [new Literal('bool', true), 'x = True'],
    'literal bool false' => [new Literal('bool', false), 'x = False'],
]);

test('gera output com literais e variáveis', function () {
    $program = new Program;
    $program->statements = [
        new Output(new Literal('str', 'Olá')),
        new Output(new Variable('nome')),
        new Output('resultado_direto'),
    ];

    $generator = new PythonGenerator;
    $code = $generator->generate($program);

    $expected = implode(PHP_EOL, [
        'print("Olá")',
        'print(nome)',
        'print(resultado_direto)',
    ]).PHP_EOL;

    expect($code)->toBe($expected);
});

test('gera if-else com indentação correta de 4 espaços', function () {
    $program = new Program;
    $program->statements = [
        new IfStatement(
            'idade >= 18',
            [new Output('Maior de idade')],
            [new Output('Menor de idade')]
        ),
    ];

    $generator = new PythonGenerator;
    $code = $generator->generate($program);

    $expected = implode(PHP_EOL, [
        'if idade >= 18:',
        '    print(Maior de idade)',
        'else:',
        '    print(Menor de idade)',
    ]).PHP_EOL;

    expect($code)->toBe($expected);
});

test('gera pass quando blocos if ou else são vazios', function () {
    $program = new Program;
    $program->statements = [
        new IfStatement('x > 0', [], []),
    ];

    $generator = new PythonGenerator;
    $code = $generator->generate($program);

    $expected = implode(PHP_EOL, [
        'if x > 0:',
        '    pass',
    ]).PHP_EOL;

    expect($code)->toBe($expected);
});

test('gera if aninhado com 8 espaços de indentação', function () {
    $program = new Program;
    $program->statements = [
        new IfStatement(
            'x > 0',
            [
                new IfStatement(
                    'x > 10',
                    [new Output('Maior que 10')],
                    [new Output('Entre 1 e 10')]
                ),
            ],
            [new Output('Zero ou negativo')]
        ),
    ];

    $generator = new PythonGenerator;
    $code = $generator->generate($program);

    $expected = implode(PHP_EOL, [
        'if x > 0:',
        '    if x > 10:',
        '        print(Maior que 10)',
        '    else:',
        '        print(Entre 1 e 10)',
        'else:',
        '    print(Zero ou negativo)',
    ]).PHP_EOL;

    expect($code)->toBe($expected);
});

test('lança exceção para statement não suportado', function () {
    $unsupportedStatement = new class implements Statement {};

    $program = new Program;
    $program->statements = [$unsupportedStatement];

    $generator = new PythonGenerator;
    $generator->generate($program);
})->throws(FlowchartException::class, 'Statement não suportado');

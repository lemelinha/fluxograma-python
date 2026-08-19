<?php

use App\CodeGenerator\Python\AstToPython;
use App\Flowchart\FlowchartGraph;
use App\Flowchart\FlowchartToAst;

function convertFixture(array $data): array
{
    $graph = new FlowchartGraph($data['nodes'], $data['edges']);
    $graph->validate();

    return AstToPython::convert(FlowchartToAst::convert($graph));
}

dataset('fixtures', function () {
    $cases = [];

    foreach (glob(__DIR__.'/../Fixtures/flowchart/*.json') as $file) {
        $cases[basename($file, '.json')] = [json_decode(file_get_contents($file), true)];
    }

    return $cases;
});

it('converte o fixture {name} para Python', function (array $data) {
    $code = convertFixture($data);
    $expected = $data['expected'];

    expect($code)->toBe($expected);
})->with('fixtures');

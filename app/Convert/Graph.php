<?php

namespace App\Convert;
use App\Exceptions\FlowchartException;
use App\Convert\Ast\{
    Program,
    Start,
    End,
    Input,
    Output,
    Assignment,
    Variable, 
    Literal
};

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
    public ?string $startNodeId = null;

    public function validate(): void {
        // verifica se existe apenas 1 node de inicio
        $startId = collect($this->nodes)->filter(fn (array $n) => $n['type'] == 'start');
        if ( count($startId) != 1 ) {
            throw new FlowchartException('Quantidade de Nodes Start inválidos');
        }
        $this->startNodeId = $startId->first()['id'];

        // verificar se o node de inicio tem aresta de saída
        if (! array_key_exists('targets', $this->nodes[$this->startNodeId]) ) {
            throw new FlowchartException('Node de início deve conter aresta de saída');
        }

        // Node de inicio apenas com 1 aresta de saída
        if ( count($this->nodes[$this->startNodeId]['targets']) > 1 ) {
            throw new FlowchartException('Node de início deve conter apenas 1 aresta de saída');
        }
            
        foreach($this->nodes as $node) {
            // verificar se existe um type
            if (! array_key_exists('type', $node)) {
                throw new FlowchartException('Node sem tipo definido');
            }

            // verificar se é um type válido
            if (! in_array($node['type'], config('convert.allowedTypeNodes'))) {
                throw new FlowchartException('Tipo de Node inválido');
            }

            // verificando node end
            if ($node['type'] == 'end') {
                if (array_key_exists('targets', $node)) {
                    throw new FlowchartException('Node end não deve ter arestas de saída');
                }
                continue;
            }

            // verificando se existe aresta de saída
            if (! array_key_exists('targets', $node)) {
                throw new FlowchartException('Node deve ter saídas');
            }

            // verificando nodes não decisão
            if ($node['type'] != 'decision') {
                if (count($node['targets']) != 1) {
                    throw new FlowchartException('Nodes devem conter apenas uma aresta de saída');
                }
            }
        }
    }

    public function toAst(): Program {
        $ast = new Program();

        $visited = [];
        $current = $this->nodes[$this->startNodeId];
        
        while (true) {
            switch ($current['type']) {
                case 'start':
                    $this->makeStart($ast);
                    break;
                case 'end':
                    $this->makeEnd($ast);
                    break;
                case 'input':
                    // verificar se existe 'data'
                    if (! array_key_exists('data', $current)) {
                        throw new FlowchartException('Node Input sem data');
                    }
                    $this->makeInput($ast, $current);
                    break;
                case 'output':
                    // verificar se existe 'data'
                    if (! array_key_exists('data', $current)) {
                        throw new FlowchartException('Node Output sem data');
                    }
                    $this->makeOutput($ast, $current);
                    break;
            }

            $visited[] = $current;
            if (
                $current['type'] != 'decision' &&
                $current['type'] != 'end'
                ) {
                $current = $this->nodes[$current['targets'][0]];
                continue;
            }

            if (count($visited) == count($this->nodes)) {
                break;
            }
        }

        return $ast;
    }

    private function makeStart(Program &$ast): void {
        $ast->statements[] = new Start();
    }
    
    private function makeEnd(Program &$ast): void {
        $ast->statements[] = new End();
    }
    
    private function makeInput(Program &$ast, array $node): void {
        // verificar se existe 'variable' e 'dataType'
        if (
            ! array_key_exists('variable', $node['data']) || 
            ! array_key_exists('dataType', $node['data'])
            ) {
            throw new FlowchartException('Input Data mal formado');
        }

        // verificar nome da variável
        if (! preg_match('/^[A-Za-z][A-Za-z_0-9]*$/', $node['data']['variable'])) {
            throw new FlowchartException('Nome de variável no Input inválido');
        }

        //verificar tipo de dado válido
        if (! in_array($node['data']['dataType'], config('convert.allowedDataTypes'))) {
            throw new FlowchartException('Tipo de input inválido');
        }

        $var = new Variable($node['data']['variable']);
        $input = new Input($node['data']['dataType'], $node['data']['label']??null);

        $ast->statements[] = new Assignment($var, $input);
    }

    private function makeOutput(Program &$ast, array $node): void {
        if (! array_key_exists('expression', $node['data'])) {
            throw new FlowchartException('Node Output sem data');
        }

        if (
            ! array_key_exists('type', $node['data']['expression']) || 
            ! array_key_exists('value', $node['data']['expression'])
            ) {
            throw new FlowchartException('Expressão do Node de Output inválida');
        }

        $outputType = $node['data']['expression']['type'];

        if (! in_array($outputType, config('convert.allowedOutputTypes'))) {
            throw new FlowchartException('Tipo de output inválido');
        }

        $outputValue = new Literal('str', '');
        switch ($outputType) {
            case 'var':
                $outputValue = new Variable($node['data']['expression']['value']);
                break;
            case 'text':
                $outputValue = new Literal('str', $node['data']['expression']['value']);
                break;
        }

        $ast->statements[] = new Output($outputValue);
    }
}

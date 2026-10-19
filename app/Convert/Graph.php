<?php

namespace App\Convert;

use App\Convert\Ast\Assignment;
use App\Convert\Ast\End;
use App\Convert\Ast\IfStatement;
use App\Convert\Ast\Input;
use App\Convert\Ast\Output;
use App\Convert\Ast\Process;
use App\Convert\Ast\Program;
use App\Convert\Ast\Start;
use App\Convert\Ast\Variable;
use App\Exceptions\FlowchartException;

class Graph
{
    /**
     * @param  array  $nodes
     *                        pattern:
     *                        [
     *                        [
     *                        'id', 'data', 'type', 'targets'
     *                        ],
     *                        ...
     *                        ]
     *                        'targets' é um array dos ids dos nodes de saída
     */
    public array $nodes = [];

    public ?string $startNodeId = null;

    public function validate(): void
    {
        // verifica se existe apenas 1 node de inicio
        $startId = collect($this->nodes)->filter(fn (array $n) => ($n['type'] ?? null) === 'start');
        if (count($startId) !== 1) {
            throw new FlowchartException('Quantidade de Nodes Start inválidos');
        }
        $this->startNodeId = $startId->first()['id'];

        // verificar se o node de inicio tem aresta de saída
        if (! array_key_exists('targets', $this->nodes[$this->startNodeId])) {
            throw new FlowchartException('Node de início deve conter aresta de saída');
        }

        // Node de inicio apenas com 1 aresta de saída
        if (count($this->nodes[$this->startNodeId]['targets']) > 1) {
            throw new FlowchartException('Node de início deve conter apenas 1 aresta de saída');
        }

        // verifica se existe pelo menos 1 node de fim
        $endCount = collect($this->nodes)->filter(fn (array $n) => ($n['type'] ?? null) === 'end');
        if ($endCount->isEmpty()) {
            throw new FlowchartException('Pelo menos 1 Node de End deve existir');
        }

        foreach ($this->nodes as $node) {
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

            // verificando nodes decisão
            if ($node['type'] == 'decision') {
                if (! isset($node['data']['expression'])) {
                    throw new FlowchartException('Node de decisão sem expressão');
                }
                if (count($node['targets'] ?? []) !== 2) {
                    throw new FlowchartException('Nó de decisão deve conter exatamente 2 saídas');
                }
                if (! isset($node['branches']['sim']) || ! isset($node['branches']['nao'])) {
                    throw new FlowchartException('Nó de decisão precisa ter saídas com labels "sim" e "nao"');
                }
            }
        }
    }

    public function toAst(): Program
    {
        $ast = new Program;
        $ast->statements = $this->buildStatements($this->startNodeId);

        return $ast;
    }

    private function buildStatements(?string $currentId, ?string $stopAtId = null): array
    {
        $statements = [];
        $visited = [];

        while ($currentId !== null && $currentId !== $stopAtId) {
            if (in_array($currentId, $visited)) {
                break;
            }
            $visited[] = $currentId;

            $node = $this->nodes[$currentId];

            if ($node['type'] === 'start') {
                $statements[] = new Start;
                $currentId = $node['targets'][0] ?? null;

                continue;
            }

            if ($node['type'] === 'end') {
                $statements[] = new End;
                break;
            }

            if ($node['type'] === 'input') {
                $statements[] = $this->createInputStatement($node);
                $currentId = $node['targets'][0] ?? null;

                continue;
            }

            if ($node['type'] === 'output') {
                $statements[] = $this->createOutputStatement($node);
                $currentId = $node['targets'][0] ?? null;

                continue;
            }

            if ($node['type'] === 'process') {
                $statements[] = $this->createProcessStatement($node);
                $currentId = $node['targets'][0] ?? null;

                continue;
            }

            if ($node['type'] === 'decision') {
                $simTarget = $node['branches']['sim'];
                $naoTarget = $node['branches']['nao'];

                $joinId = $this->findConvergenceNode($simTarget, $naoTarget);

                $thenStatements = $this->buildStatements($simTarget, $joinId);
                $elseStatements = $this->buildStatements($naoTarget, $joinId);

                $statements[] = new IfStatement(
                    $node['data']['expression'],
                    $thenStatements,
                    $elseStatements
                );

                $currentId = $joinId;

                continue;
            }

            break;
        }

        return $statements;
    }

    private function createInputStatement(array $node): Assignment
    {
        if (
            ! array_key_exists('variable', $node['data']) ||
            ! array_key_exists('dataType', $node['data'])
        ) {
            throw new FlowchartException('Input Data mal formado');
        }

        if (! preg_match('/^[A-Za-z][A-Za-z_0-9]*$/', $node['data']['variable'])) {
            throw new FlowchartException('Nome de variável no Input inválido');
        }

        if (! in_array($node['data']['dataType'], config('convert.allowedDataTypes'))) {
            throw new FlowchartException('Tipo de input inválido');
        }

        $var = new Variable($node['data']['variable']);
        $input = new Input($node['data']['dataType'], $node['data']['label'] ?? null);

        return new Assignment($var, $input);
    }

    private function createOutputStatement(array $node): Output
    {
        if (! array_key_exists('expression', $node['data'])) {
            throw new FlowchartException('Node Output sem data');
        }

        return new Output($node['data']['expression']);
    }

    private function createProcessStatement(array $node): Process
    {
        if (! array_key_exists('variable', $node['data'])) {
            throw new FlowchartException('Node Process sem data');
        }

        if (! array_key_exists('expression', $node['data'])) {
            throw new FlowchartException('Node Process sem expressão');
        }

        $var = new Variable($node['data']['variable']);

        return new Process($var, $node['data']['expression']);
    }

    private function findConvergenceNode(string $nodeA, string $nodeB): ?string
    {
        $visitedA = $this->getReachableNodes($nodeA);
        $visitedB = $this->getReachableNodes($nodeB);

        $common = array_values(array_intersect($visitedA, $visitedB));

        foreach ($common as $id) {
            if (isset($this->nodes[$id]) && $this->nodes[$id]['type'] !== 'end') {
                return $id;
            }
        }

        return null;
    }

    private function getReachableNodes(string $startId): array
    {
        $visited = [];
        $queue = [$startId];

        while (! empty($queue)) {
            $curr = array_shift($queue);
            if (in_array($curr, $visited) || ! isset($this->nodes[$curr])) {
                continue;
            }
            $visited[] = $curr;
            foreach ($this->nodes[$curr]['targets'] ?? [] as $targetId) {
                $queue[] = $targetId;
            }
        }

        return $visited;
    }
}

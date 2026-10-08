# Análise Completa do Projeto: Laravel Flowchart to Code Converter

## Visão Geral

O projeto é uma API Laravel que converte diagramas de fluxo (ReactFlow format) em código Python/JS.

**Stack:** Laravel 13 + PHP 8.3 + Pest (testing) + Inertia v3 + React 19 + Tailwind v4

**Objetivo:** Traduzir fluxogramas em código Python e JavaScript a partir da mesma AST.

## Arquitetura do Sistema

### Camada de Domínio (app/Convert/)

**FlowchartGraph.php** - Converte nodes/edges em estrutura de grafo
- Valida IDs de nodes (lança exceção se faltando ou duplicados)
- Valida estrutura das edges (source/target obrigatórios, nodes existenciais)
- Lancar FlowchartException em erros

**Graph.php** - Núcleo do sistema, contém:
- Propriedade `$nodes` (array) e `$startNodeId`
- `validate()`: verifica consistência estrutural
  - Existe exatamente 1 node de start
  - Node de start tem aresta de saída
  - Node de start tem apenas 1 aresta de saída
  - Todos os nodes têm tipo definido e são tipos válidos
  - Node `end` não deve ter arestas de saída
  - Nodes não-decisão devem ter exatamente 1 aresta de saída
- `toAst()`: traverse grafo -> AST (transição linear)
  - Percorre do node de start seguindo as arestas
  - Converte cada tipo de node (start, end, input, output) em declarações AST
  - Tem proteção contra loops infinitos (contador de visited)

### AST (app/Convert/Ast/ - Classes de dados imutáveis)

- **Program** - `{ statements }` - contém todas as declarações
- **Start** - nó inicial (statement)
- **End** - nó final (statement)
- **Input** - `{ type, text }` - usado em Assignment
- **Output** - `{ expression: Variable|Literal }` - expressão de saída
- **Assignment** - `{ var: Variable, expression: Variable|Literal|Input }` - atribuição de variável
- **Variable** - `{ name }` - nome da variável
- **Literal** - `{ type, value }` - valor literal (string, number, bool, float)

## Controller (app/Http/Controllers/ConvertController.php)

Estado atual:
- Usa hardcoded fixtures (nodes/edges definidos no código, não vêm do request)
- Validação comentada (linhas 28-31 têm $request->validate() comentado)
- Usa `dd($ast)` quebra o contrato JSON da API
- Retorno de erro incompleto no catch (apenas mensagem, sem code/details padronizados)
- Rota é GET sem body

## Configuração (config/convert.php)

```php
allowedTypeNodes: [start, end, input, output]
allowedDataTypes: [str, int, float, bool]
allowedOutputTypes: [var, text]
```

## Rotas (routes/api.php)

```
GET /api/convert/{sourceLanguage}/to/{targetLanguage}
```

## Frontend (resources/js/)

- Inertia + React
- Wayfinder para geração de rotas/ações
- Estrutura: actions/, routes/, wayfinder/, types/, pages/
- `app.tsx` é a entrada Inertia

## Decisões Confirmadas (de CONTEXTO.md)

1. **Método HTTP:** Migrar de GET para POST com `{nodes, edges}` no body
2. **Linguagens de destino:** Python + JS a partir da mesma AST (depois expandir para mais)
3. **Validação:** Apenas estrutural (sem análise semântica de variáveis)
4. **Exceções:** Domínio lança exceção tipada com `errorCode` string
5. **Resposta HTTP:** Controller/handler traduz para envelope `ApiResponse`
6. **try/catch:** Apenas na borda (controller), não no domínio

## O Que Funciona (baseado no código real)

1. Conversão de grafo nodes->edges funciona
2. Validação de tipos de nodes (start, end, input, output)
3. Validação de arestas (source/target existence)
4. Criação de AST linear a partir do grafo
5. Estrutura de AST completa (Program, Start, End, Input, Output, Assignment, Variable, Literal)
6. Envelope de resposta ApiResponse (success/error)
7. FlowchartException como exceção de domínio

## O Que Precisa de Trabalho (baseado no código real)

1. **Controller hardcoded:** nodes/edges são fixtures, não vêm do request
2. **Rota GET sem body:** precisa migrar para POST com validação
3. **dd($ast) no catch:** quebra o contrato JSON da API
4. **Validação comentada:** $request->validate() está comentada
5. **Graph::$nodes sem inicialização:** declarado como `public array $nodes;` sem default `[]`
6. **Formato de expressão desalinhado:** controller gera `{type: 'var'|'text', value}` mas Output/Assignment esperam formatos diferentes
7. **Import Exception:** Já está na linha 7, mas o catch na linha 85 usa Exception do namespace global
8. **Sem testes de conversão:** tests/ só tem ExampleTest

## Formato de Expressão - Análise Detalhada

Há um desalinhamento entre o que o controller gera e o que as classes da AST esperam:

**Controller atual gera (linhas 51-55):**
```php
'expression' => [
    'type' => 'var',
    'value' => 'nome'
]
```

**Classes da AST esperam:**
- **Output:** `{ expression: Variable|Literal }` - a propriedade `expression` deve ser um objeto Variable ou Literal
- **Assignment:** `{ var: Variable, expression: Variable|Literal|Input }` - precisa de `var` separado

**A definição correta (confirmada na entrevista):**
- **Output:** `{ type: 'var'|'text', value: any }` - tipo de dado mais valor
- **Assignment:** `{ var: {name: string}, expression: {type: 'var'|'text', value: any} }` - variável + expressão

O Assignment precisa de ambos (variável nomeada + valor), enquanto o Output só precisa do valor (que pode ser uma referência a variável ou texto puro).

## Recomendações para MVP

1. **Desbloquear validação no Controller** e usar `$request->validate()`
2. **Mudar rota** de GET para POST em `routes/api.php`
3. **Remover dd($ast)`** e usar `errorResponse` no catch
4. **Inicializar Graph::$nodes** com `[]`
5. **Unificar formato de expressão** entre Assignment e Output (definir Assignment como `{var, expression: {type, value}}` e Output como `{type, value}`)
6. **Implementar códigos de erro padronizados** (INVALID_* ao invés das atualizadas "laguange")
7. **Adicionar suporte a decision nodes** (labels true/false nas edges)

## Conclusão

O projeto tem uma arquitetura sólida com separação clara entre domínio (Graph/AST) e camada HTTP (Controller/ApiResponse). O código domain está bem estruturado, com exceções tipadas e um percurso AST bem definido. O controller está em estado preliminar com fixtures hardcoded e validação comentada - o que é esperado para um protótipo de iniciação científica.

O sistema está pronto para converter fluxogramas estruturalmente válidos em código Python/JS a partir da mesma AST, que é o objetivo principal definido em CONTEXTO.md. Os próximos passos são conectar o controller ao request real, migrar a rota e garantir consistência de formatos.
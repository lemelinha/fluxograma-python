# CONTEXTO — leia no início de cada sessão

## O projeto
Sistema web que traduz fluxogramas (ReactFlow) em código Python/JS. Este repositório contém apenas a API de tradução.

**Stack:** Laravel 13 + PHP 8.3 + Pest (ver AGENTS.md para comandos).

**Funcionamento:**
1. Recebe `{nodes, edges}` via requisição POST na rota `/api/convert/{sourceLanguage}/to/{targetLanguage}`
2. Converte o grafo em AST (Abstract Syntax Tree) através de classes de domínio
3. Gera código Python a partir da mesma AST (JS virá do mesmo caminho)

**API:** GET/PUT `/api/convert/{sourceLanguage}/to/{targetLanguage}`
- Linguagens permitidas definidas em `config/convert.php`
- Fonte: `flowchart`
- Destino: `python` (ou `js` a partir da mesma AST)

## Regra de atuação (standing)
**Só ler e analisar. Não criar, editar ou remover código-fonte.**
Exceções já autorizadas antes: criar docs de texto quando pedido (`PENDENCIAS.txt`, este arquivo).

**Estrutura do código:**

### Domínio (app/Convert/)
- `FlowchartGraph.php` — Converte nodes/edges em estrutura de grafo
  - Valida IDs de nodes (exceção se faltando ou duplicados)
  - Valida estrutura das edges (source/target obrigatórios, nodes existenciais)
  - Lancar `FlowchartException` em erros de domínio

- `Graph.php` — Núcleo da conversão
  - Propriedade `$nodes` (já inicializada como `[]`)
  - `$startNodeId` — ID do nó inicial
  - `validate()`: Verifica consistência estrutural
    - Existe exatamente 1 node de start
    - Node de start tem aresta de saída
    - Node de start tem apenas 1 aresta de saída
    - Todos os nodes têm tipo definido e são tipos válidos (config `allowedTypeNodes`)
    - Node `end` não deve ter arestas de saída
    - Nodes não-decisão devem ter exatamente 1 aresta de saída
  - `toAst()`: Traverse grafo → AST (transição linear)
    - Percorre do node de start seguindo as arestas
    - Converte cada tipo de node em declarações AST
    - Tem proteção contra loops infinitos

### AST (app/Convert/Ast/)
Classes de dados imutáveis que formam a árvore sintática abstrata:
- `Program` — `{ statements }` — contém todas as declarações
- `Start` — Nó inicial (statement)
- `End` — Nó final (statement)
- `Input` — `{ type, text }` — usado em Assignment
- `Output` — `{ expression: Variable|Literal }` — expressão de saída
- `Assignment` — `{ var: Variable, expression: Variable|Literal|Input }` — atribuição de variável
- `Variable` — `{ name }` — nome da variável
- `Literal` — `{ type, value }` — valor literal (string, number, bool, float)

### Controlador (app/Http/Controllers/ConvertController.php)
Controller que processa a conversão. **Estado atual:**
- Recebe sourceLanguage e targetLanguage como parâmetros de route
- Usa `ApiResponse` trait para respostas de sucesso/erro
- **Ainda usa fixtures hardcoded** para nodes/edges (não vêm do request)
- Validação `$request->validate()` está **comentada**
- Usa `dd($ast)` quebra o contrato JSON da API
- Catch usa `Exception` global (já importado)

**Decisões já definidas:**
- Contrato: migrar de GET para **POST** com `{nodes, edges}` em JSON
- Alvos: **Python + JS** a partir da mesma AST
- Validação: só **estrutural básica** (sem análise semântica de variáveis)
- Erros: domínio lança **exceção tipada** (com `errorCode` string)
- controller/handler traduz p/ envelope `ApiResponse`
- `try/catch` só na borda (controller)

### Configuração (config/convert.php)
- `sourceLanguages`: `['flowchart']`
- `targetLanguages`: `['python']`
- `allowedTypeNodes`: `[start, end, input, output]`
- `allowedDataTypes`: `[str, int, float, bool]`
- `allowedOutputTypes`: `[var, text]`

### Rotas (routes/api.php)
```
GET /api/convert/{sourceLanguage}/to/{targetLanguage}
```
**Próxima etapa:** Migrar para POST com body JSON `{nodes, edges}` e religar validação.

### Exceções (app/Exceptions/FlowchartException.php)
Exceção de domínio que estende `Exception`. Pode ser lançada com códigos de erro tipados.

### Traits (app/Traits/ApiResponse.php)
Trait que fornece métodos para respostas HTTP padronizadas:
- `successResponse()` — envelope `{status: 'success', message, data}`
- `errorResponse()` — envelope `{status: 'error', message, details, code}`

### Testes (tests/)
- Apenas `ExampleTest` — **sem testes de conversão** ainda
- Usa `RefreshDatabase` para testes Feature

### Frontend (resources/js/)
- Inertia + React
- Wayfinder para geração de rotas/ações
- Estrutura: actions/, routes/, wayfinder/, types/, pages/
- `app.tsx` é a entrada Inertia
- **API só** — frontend separado, ReactFlow gerenciado pelo frontend

## Decisões Técnicas Fixas (06/10/2026)
- Blocos: básico linear (start/end, input, output, atribuição) + decisão if/else
- Alvos: Python + JS a partir da mesma AST
- Validação: somente estrutural básica (sem análise semântica de variáveis)
- Erros: domínio lança exceção tipada com errorCode string
- controller/handler traduz para envelope ApiResponse
- try/catch só na borda
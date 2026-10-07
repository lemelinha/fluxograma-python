# CONTEXTO — leia no início de cada sessão

## O projeto
Iniciação científica: sistema web que traduz fluxogramas (ReactFlow) em
código Python/JS. **Este repositório é só a API de tradução.**
Stack: Laravel 13 + PHP 8.3 + Pest (ver `AGENTS.md` p/ comandos).

## Regra de atuação (standing)
**Só ler e analisar. Não criar, editar ou remover código-fonte.**
Exceções já autorizadas antes: criar docs de texto quando pedido
(`PENDENCIAS.txt`, este arquivo) e a recuperação do git.
Controller: `GET /api/convert/{source}/to/{target}` →
`ConvertController@convert`. Alvos em `config/convert.php`.

## Decisões fechadas em entrevista (06/10/2026)
- Blocos: básico linear (start/end, input, output, atribuição) + decisão if/else.
- Contrato: migrar de GET para **POST** com `{nodes, edges}` em JSON.
- Alvos: **Python + JS** a partir da mesma AST.
- Validação: só **estrutural básica** (sem análise semântica de variáveis).
- Erros: domínio lança **exceção tipada** (com `errorCode` string);
  controller/handler traduz p/ envelope `ApiResponse`. `try/catch` só na borda.

## Estado atual da branch `remodelagem-de-tudo-fodase`
- Controller ignora o corpo (validação comentada) e usa **fixture hardcoded**;
  retorna placeholder `['code' => 'código']`.
- `FlowchartGraph::toGraph()` monta o grafo; `Graph::toAst()` **não existe**.
- Em ajuste: `try/catch` no controller (faltava `return` no catch),
  exception experimental `App\Exceptions\test` (renomear p/ nome de domínio).
- Pendências técnicas: ver `PENDENCIAS.txt` (15 itens).

## Atenção estrutural
A `main` local/remota contém uma **refatoração** (`app/Flowchart/`,
`app/Ast/`, `app/CodeGenerator/`, `ConverterController`, testes com fixtures)
que **não existe** nesta branch (estrutura antiga `app/Convert/`).
Merge futuro = conflito estrutural; comparar antes de evoluir.

## Git
Recuperado de corrupção em 06/10/2026 (7 loose objects vazios).
Branches: `main`, `main-local-antiga` (1 commit local à frente),
`remodelagem-conversao-json-grafo-ast` (histórico intacto),
`remodelagem-de-tudo-fodase` (ativa; histórico refeito como snapshot).
Remoto: `github.com/lemelinha/fluxograma-python`.

# fluxograma-python

Conversor bidirecional de fluxogramas em código Python.

Projeto de Iniciação Científica desenvolvido no **IFSP — Campus Jacareí**, com foco no ensino de lógica de programação para iniciantes.

## Sobre o projeto

Aprender lógica de programação exige, muitas vezes, dominar a sintaxe de uma linguagem antes de compreender o raciocínio algorítmico. Este projeto propõe outro caminho: o estudante **desenha o algoritmo na forma de um fluxograma** — representação visual e intuitiva — e o sistema **converte esse desenho em código Python** automaticamente.

A conversão é a essência desta pesquisa. O repositório contém a **API responsável pela conversão**. A interface onde o usuário desenha o fluxograma é um frontend separado, com editor visual, que envia o diagrama para esta API.

## Objetivos

### Geral

Desenvolver uma API capaz de converter fluxogramas em código Python, contribuindo para o aprendizado de lógica de programação por meio de uma abordagem visual.

### Específicos

- Estabelecer uma representação padronizada do fluxograma, capaz de ser interpretada por sistemas computacionais.
- Implementar a conversão automática do fluxograma para código Python.
- Implementar a conversão reversa — do código Python de volta ao fluxograma — completando o ciclo bidirecional.
- Disponibilizar a conversão por meio de uma API HTTP, reutilizável por aplicações externas.

## Público-alvo

- Estudantes de ensino médio técnico e graduação que estejam iniciando no estudo de programação.
- Docentes de disciplinas de lógica de programação e algoritmos.
- Pessoas em início de carreira na área de tecnologia que preferem uma primeira aproximação visual com algoritmos.

## Stack tecnológica

| Camada | Tecnologia |
|---|---|
| Backend / API | PHP + Laravel |
| Banco de dados | PostgreSQL |
| Infraestrutura | Docker (Nginx + PHP-FPM) |
| Testes | Pest (PHPUnit) |
| Frontend (separado) | React + React Flow |

## Modelagem do sistema

```
        Frontend (React + React Flow)
      ┌──────────────────────────────────┐
      │  Usuário desenha o fluxograma    │
      │  Editor exporta o diagrama       │
      └───────────────┬──────────────────┘
                      │  enviar diagrama
                      ▼
        API de conversão (este repositório)
      ┌──────────────────────────────────┐
      │  Recebe a representação do       │
      │  fluxograma                      │
      │                                  │
      │  Converte para AST               │
      │  (Árvore Sintática Abstrata)     │
      │                                  │
      │  Gera o código Python            │
      └───────────────┬──────────────────┘
                      │  código resultante
                      ▼
        Resposta para o frontend
      ┌──────────────────────────────────┐
      │  Usuário visualiza o código      │
      │  Python equivalente              │
      └──────────────────────────────────┘
```

O diagrama acima apresenta o fluxo principal do sistema em alto nível. Os detalhes internos da conversão — como o diagrama é interpretado e transformado em código — são fruto da pesquisa e não fazem parte deste documento.

## Roadmap

### Fase 1 — Conversão fluxograma → Python

- [ ✔️ ] Definir a representação padronizada do fluxograma (nós e arestas).
- [ ✔️ ] Implementar a leitura e validação do diagrama recebido.
- [ ] Converter o fluxograma em Árvore Sintática Abstrata (AST).
- [ ] Gerar o código Python a partir da AST.
- [ ] Expor o fluxo completo pela API.
- [ ] Escrever testes automatizados de conversão.

### Fase 2 — Conversão reversa (Python → fluxograma)

- [ ] Interpretar código Python como entrada da API.
- [ ] Construir a AST correspondente ao código.
- [ ] Gerar a representação do fluxograma a partir da AST.

### Fase 3 — Integração com o frontend

- [ ] Conectar o editor visual de fluxogramas à API.
- [ ] Exibir o código gerado na interface.
- [ ] Validar a experiência de uso com estudantes.

## Estrutura do repositório

```
src/                # aplicação Laravel (API de conversão)
Dockerfile          # imagem do serviço PHP
docker-compose.yaml # orquestração dos serviços
```

## Executando o projeto

```bash
docker compose up -d --build
cd nginx/certs
mkcert icfoxleme.dev "*.icfoxleme.dev"
```

O projeto estará acessível em https://api.icfoxleme.dev

## Licença

MIT — ver arquivo [LICENSE](LICENSE).

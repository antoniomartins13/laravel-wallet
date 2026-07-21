# ADR-0007 — Docker Compose como ambiente padrão de desenvolvimento

- **Status**: aceito
- **Data**: 2026-07-20

## Contexto

O desafio lista Docker como diferencial e exige a aplicação rodando no dia da
entrevista. O ambiente precisa subir de forma idêntica em qualquer máquina,
sem depender de PHP/MySQL/Node instalados no host.

## Decisão

Ambiente completo definido em `docker-compose.yml` com quatro serviços:
`app` (PHP-FPM 8.3, Dockerfile próprio), `nginx`, `mysql` (8.0, volume
nomeado, healthcheck) e `node` (build/dev do frontend). Meta: do clone ao
sistema rodando em três comandos documentados no README.

## Alternativas consideradas

- **Laravel Sail** — funciona, mas é um wrapper pronto; compose escrito à mão
  demonstra domínio da ferramenta e permite ajustar nginx/healthchecks.
- **Ambiente no host** — "funciona na minha máquina"; risco inaceitável para
  uma demo ao vivo.

## Consequências

- Reprodutibilidade total; a demo da entrevista começa com
  `docker compose up -d`.
- Healthcheck do MySQL ordena a subida (app espera o banco aceitar conexões).
- Custo de manutenção do Dockerfile próprio — aceito pelo valor demonstrativo.

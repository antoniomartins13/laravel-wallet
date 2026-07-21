# ADR-0005 — Fluxo Git: PRs obrigatórios, Conventional Commits e CI bloqueante

- **Status**: aceito
- **Data**: 2026-07-20

## Contexto

Repositório de desenvolvedor único, mas avaliado como se fosse código de time
(o desafio prevê code review "como se você já fosse do nosso time"). O
histórico do Git é parte da entrega.

## Decisão

- `main` protegida por ruleset: PR obrigatório (0 aprovações), status check
  `tests` obrigatório, force push e deleção bloqueados.
- Ruleset adicional restringe criação de branches aos padrões
  `feat/*`, `fix/*`, `chore/*`, `docs/*`, `refactor/*`, `test/*`.
- **Conventional Commits** obrigatórios, com escopo
  (ex.: `feat(transfer): add pessimistic lock`).
- Enforcement em duas camadas: hooks locais (Husky: `commit-msg` com
  commitlint, `pre-push` valida nome da branch) + rulesets no servidor.
- CI (GitHub Actions) roda `php artisan test` em todo PR; merge bloqueado sem
  verde.

## Alternativas consideradas

- **Exigir 1 aprovação no PR** — impossível aprovar o próprio PR; travaria o
  fluxo. Em time, seria 1+ com CODEOWNERS.
- **Commits livres** — histórico ilegível e sem changelog derivável.

## Consequências

- Cada feature vira um PR com testes verdes; o histórico narra o projeto.
- Hooks locais dão feedback imediato; rulesets garantem a regra mesmo se os
  hooks forem pulados (`--no-verify`).
- Pequeno atrito por commit — aceito deliberadamente como demonstração de
  disciplina de engenharia.

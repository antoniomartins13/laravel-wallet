# Carteira Financeira — Desafio Grupo Adriano Cobuccio

Monorepo: `backend/` (Laravel API) + `frontend/` (React TS + Tailwind + Vite).
MySQL 8 via Docker no runtime; SQLite `:memory:` nos testes.

## Comandos

```bash
docker compose up -d                 # sobe o ambiente completo
docker compose exec app php artisan test        # suíte de testes
docker compose exec app php artisan migrate     # migrations
cd frontend && npm run dev           # front em modo dev
cd frontend && npx tsc --noEmit      # checagem de tipos
```

## Regras invioláveis do domínio

- **Dinheiro é SEMPRE inteiro em centavos** (`int` no PHP, `bigint` no banco,
  `number` inteiro no TS). Nunca float. Formatação em R$ só na exibição.
- **CPF é string** — nunca inteiro (zeros à esquerda).
- **PKs são UUID** (`HasUuids` nos models).
- **Ledger imutável**: nunca UPDATE/DELETE em `transactions`. Reversão = nova
  transação `reversal` com `reference_id`. Saldo só muda dentro de
  `DB::transaction` com `lockForUpdate`.
- Enums nativos do PHP para `TransactionType` e `TransactionStatus`.

## Git

- Branches: `feat/*`, `fix/*`, `chore/*`, `docs/*`, `refactor/*`, `test/*`.
- Conventional Commits **com escopo**: `feat(transfer): add pessimistic lock`.
- `main` só recebe código via PR com CI verde. Nunca commitar direto na main.
- Toda mudança de schema atualiza `docs/database/schema.dbml` no mesmo PR.
- Decisões arquiteturais novas ganham ADR em `docs/adr/` (próximo número).

## Fluxo de desenvolvimento

- TDD: escreva o teste antes da implementação, veja falhar, implemente.
- Camadas: Controller → FormRequest → DTO → Service → Repository → Model.
  Detalhes na skill `backend-architecture`.
- UI segue a skill `design-system` (paleta navy/dourado, estilo BTG).

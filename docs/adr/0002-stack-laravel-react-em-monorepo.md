# ADR-0002 — Laravel (API) + React TS (SPA) em monorepo

- **Status**: aceito
- **Data**: 2026-07-20

## Contexto

O desafio pede PHP/Laravel/SQL e uma interface funcional de carteira
financeira. É um projeto de escopo fechado, desenvolvido e avaliado como uma
unidade (code review + demo ao vivo).

## Decisão

- **Backend**: Laravel na última versão estável, servindo uma API REST.
- **Frontend**: SPA em React + TypeScript + Tailwind, consumindo a API.
- **Estrutura**: monorepo com `backend/` e `frontend/` no mesmo repositório.

## Alternativas consideradas

- **Blade + Livewire (full-stack Laravel)** — menos código de integração,
  porém a separação API/SPA demonstra arquitetura de front e back
  independentes e reflete o padrão dominante no mercado.
- **Dois repositórios** — separação máxima, mas atrito desnecessário para um
  projeto avaliado como um todo (dois clones, dois CIs, PRs desconexos).

## Consequências

- Um único `docker compose up` sobe o sistema completo; um único CI cobre tudo.
- Autenticação passa a ser um problema de SPA (CORS, cookies cross-origin) —
  tratado no ADR-0003.
- TypeScript no front dá tipagem de ponta a ponta espelhando DTOs do back.

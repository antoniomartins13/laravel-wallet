# ADR-0003 — Autenticação via Sanctum em modo SPA (cookies) com Breeze API

- **Status**: aceito
- **Data**: 2026-07-20

## Contexto

O sistema é uma carteira financeira: roubo de sessão significa roubo de
dinheiro. A SPA React precisa autenticar-se contra a API Laravel, e o desafio
avalia segurança explicitamente.

## Decisão

Usar **Laravel Sanctum no modo SPA** (sessão via cookie) com o scaffolding do
**Breeze em modo `api`** (registro, login, logout, reset de senha).

## Alternativas consideradas

- **Sanctum com tokens Bearer em `localStorage`** — qualquer XSS bem-sucedido
  lê o token via JavaScript e o exfiltra. Descartado para contexto financeiro.
- **JWT (pacotes de terceiros)** — complexidade extra (rotação, revogação,
  blacklist) sem ganho para SPA de primeiro-partido no mesmo domínio raiz.
- **Passport (OAuth2)** — dimensionado para autorização de terceiros; excesso
  de máquina para um único cliente próprio.

## Consequências

- Cookie de sessão `httpOnly` + `SameSite`: JavaScript não consegue ler a
  credencial; XSS não rouba a sessão.
- Proteção CSRF nativa do Laravel (o front chama `/sanctum/csrf-cookie` antes
  do login e envia o token nas mutações).
- Exige configuração correta de `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN` e
  CORS com `supports_credentials` — documentada no `.env.example`.
- Se um dia houver clientes móveis/terceiros, o mesmo Sanctum emite tokens de
  API sem troca de biblioteca.

# ADR-0004 — Telescope restrito a desenvolvimento (defesa em três camadas)

- **Status**: aceito
- **Data**: 2026-07-20

## Contexto

O Telescope atende ao critério de observabilidade do desafio (queries,
requests, exceptions), mas grava payloads e bindings — dados sensíveis em um
sistema financeiro. Expor `/telescope` em produção seria um vazamento grave.

## Decisão

Telescope instalado como dependência de desenvolvimento e protegido por três
camadas independentes:

1. **`require-dev`** — `composer install --no-dev` em produção não baixa o
   pacote.
2. **Registro condicional** — auto-discovery desativado
   (`dont-discover: laravel/telescope`); providers registrados manualmente no
   `AppServiceProvider` apenas quando `APP_ENV=local`.
3. **Gate** — `viewTelescope` autoriza somente e-mails listados, caso as
   camadas anteriores falhem por erro humano.

## Alternativas consideradas

- **Telescope em produção com gate + pruning** — viável, mas o padrão de
  mercado para produção é APM/logs estruturados; Telescope é ferramenta de
  desenvolvimento.

## Consequências

- Boot de produção não referencia classes ausentes (o registro condicional
  evita "class not found" com `--no-dev`).
- No CI (`APP_ENV=testing`), o pacote existe mas não registra — sem
  interferência nos testes.
- Observabilidade de produção fica a cargo dos logs estruturados do canal
  `financial` (decisão a detalhar quando implementada).

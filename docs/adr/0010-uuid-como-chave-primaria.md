# ADR-0010 — UUID como chave primária

- **Status**: aceito
- **Data**: 2026-07-20

## Contexto

`users.id`, `wallets.id` e `transactions.id` são expostos em URLs públicas da
API (ex.: extrato de uma carteira, detalhe de uma transação). IDs sequenciais
previsíveis vazam informação de negócio e facilitam enumeração.

## Decisão

Todas as chaves primárias usam UUID em vez de auto-incremento inteiro. No
Laravel, os models usam a trait `HasUuids`, que gera e atribui o UUID antes da
inserção.

## Alternativas consideradas

- **Auto-incremento (`BIGINT UNSIGNED AUTO_INCREMENT`)** — mais compacto e
  levemente mais rápido em índices, mas expõe o volume total de registros
  (`/wallets/1042`) e permite enumeração sequencial de recursos de outros
  usuários.
- **ID interno auto-incremento + UUID público separado** — evita o overhead
  de índice de UUID como PK, mas duplica a chave em toda tabela e todo join
  passa a exigir a coluna extra. Complexidade desnecessária para o volume de
  dados deste projeto.

## Consequências

- Nenhum endpoint revela quantos usuários, carteiras ou transações existem.
- Merge/seed de dados entre ambientes (dev, staging) não colide em PK.
- Índices sobre UUID tendem a fragmentar mais que auto-incremento — mitigado
  porque `HasUuids` (Laravel 13) gera UUIDv7 por padrão, que é ordenável por
  tempo de criação e mantém a inserção no índice majoritariamente sequencial.

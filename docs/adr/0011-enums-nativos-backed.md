# ADR-0011 — Enums nativos do PHP (backed enums) para tipo e status

- **Status**: aceito
- **Data**: 2026-07-20

## Contexto

`transactions.type` (`deposit`, `transfer_in`, `transfer_out`, `reversal`) e
`transactions.status` (`pending`, `completed`, `reversed`, `failed`) têm um
conjunto fechado e conhecido de valores válidos. Um valor fora desse conjunto
não tem significado de negócio e não deve conseguir chegar ao banco.

## Decisão

`TransactionType` e `TransactionStatus` são backed enums nativos do PHP
(`enum TransactionType: string`), usados como type-hint em toda a camada de
domínio (models, actions, form requests) e persistidos via cast de enum do
Eloquent.

## Alternativas consideradas

- **String livre + validação apenas no Form Request** — a validação de
  entrada da API é contornável por qualquer código interno que escreva
  direto no model/repositório; o enum nativo torna o valor inválido
  impossível de representar em tempo de compilação, não apenas de request.
- **Enum de banco (`ENUM(...)` do MySQL)** — amarra o conjunto de valores ao
  schema; adicionar um valor exige migration com `ALTER TABLE` reescrevendo
  a coluna, e o valor só é validado no INSERT, não em tempo de
  desenvolvimento no PHP.

## Consequências

- Um `match` não exaustivo sobre um backed enum é erro de tipo, não bug
  silencioso em produção — o compilador força tratar todo caso.
- Autocomplete e refactor seguro em toda a IDE para os valores possíveis.
- Adicionar um novo tipo/status é alterar o enum PHP; a coluna do banco
  permanece `VARCHAR`, sem migration estrutural.

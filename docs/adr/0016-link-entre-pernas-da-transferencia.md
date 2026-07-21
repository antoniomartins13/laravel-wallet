# ADR-0016 — `related_transaction_id`: link explícito entre as duas pernas de uma transferência

- **Status**: aceito
- **Data**: 2026-07-21

## Contexto

Uma transferência gera duas linhas no ledger: `transfer_out` (na carteira de
origem) e `transfer_in` (na carteira de destino) — ver ADR-0012. Reverter
"a transferência" exige desfazer as duas pernas atomicamente, dado o ID de
**qualquer uma** delas (o participante que pede a reversão pode ser o
remetente ou o destinatário — ver checklist de segurança, "só participantes
podem reverter"). Sem um link direto entre as duas linhas, encontrar a
"outra perna" exigiria heurística (`related_wallet_id` + `amount` +
proximidade de `created_at`) — arriscado: duas transferências do mesmo
valor entre as mesmas duas pessoas em sequência poderiam ser confundidas,
revertendo a transação errada.

## Decisão

Coluna `related_transaction_id` (UUID, nullable, FK para `transactions.id`)
adicionada via migration própria
(`add_related_transaction_id_to_transactions_table`), não editando a
migration original de `transactions` — essa já tinha sido mergeada em PRs
anteriores (`feat/deposit`, `feat/transfer`). `TransferService` cria `transfer_out`, depois
`transfer_in` já apontando pra ela via `related_transaction_id`, e por fim
atualiza `transfer_out` apontando de volta pra `transfer_in` — link
bidirecional. `ReversalService` usa esse link para localizar a perna irmã
com uma query direta (`$leg->relatedTransaction()->lockForUpdate()
->firstOrFail()`), sem heurística.

## Alternativas consideradas

- **Heurística por `related_wallet_id` + `amount` + janela de tempo** —
  não precisa de coluna nova, mas é ambígua exatamente no caso que mais
  importa (transferências repetidas entre as mesmas partes) e erra o tipo
  de coisa que não pode errar num sistema financeiro.
- **Reaproveitar `reference_id`** — já existe e também é uma FK para
  `transactions`, mas seu significado é "transação original que esta
  reverte" (só usado em linhas `type: reversal`). Usar o mesmo campo para
  "perna irmã da transferência" sobrecarregaria uma coluna com dois
  significados distintos dependendo do `type` — pior para quem lê o schema
  depois.

## Consequências

- Reversão de transferência é uma busca direta (uma FK), não uma heurística
  — determinística e correta mesmo com transferências repetidas idênticas.
- Schema evoluiu depois de já ter duas features (deposit, transfer)
  construídas em cima dele — sinal de que o desenho inicial não previu a
  reversão de transferência a fundo; aceito como parte natural de descobrir
  requisitos durante o desenvolvimento, documentado aqui em vez de
  silenciosamente reescrever a migration antiga.

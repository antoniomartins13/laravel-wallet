# ADR-0013 — Saldo materializado em `wallets.balance` + ledger como fonte de verdade

- **Status**: aceito
- **Data**: 2026-07-20

## Contexto

Consultar o saldo de uma carteira é a operação mais frequente do sistema
(toda tela, todo depósito/transferência precisa saber o saldo atual antes de
agir). Somar o histórico completo de `transactions` a cada leitura degrada
com o tamanho do ledger. Ao mesmo tempo, o ADR-0012 estabelece o ledger como
o registro imutável e auditável dos eventos.

## Decisão

`wallets.balance` guarda o saldo corrente, atualizado a cada transação
efetivada, mas **não é a fonte de verdade** — é uma projeção do ledger para
leitura rápida. Toda escrita que altera `balance` acontece dentro de uma
transação de banco (`DB::transaction`) com lock pessimista
(`SELECT ... FOR UPDATE`) na linha da carteira, garantindo que a leitura do
saldo, a inserção da linha em `transactions` e a atualização de `balance`
sejam atômicas e serializadas por carteira.

## Alternativas consideradas

- **Sem coluna de saldo, sempre somar o ledger (`SUM(amount)`)** — elimina o
  risco de saldo materializado divergir do ledger, mas transforma a leitura
  mais comum do sistema em uma agregação sobre uma tabela que só cresce.
  Inaceitável para uma carteira com uso contínuo.
- **Cache externo (Redis) para o saldo** — resolve performance, mas introduz
  uma segunda fonte de estado para manter consistente com o banco, com sua
  própria janela de invalidação/corrida — complexidade maior sem ganho real
  frente a uma coluna na própria linha já lockada pela transação.

## Consequências

- Leitura de saldo é O(1) (uma linha), sem tocar o ledger.
- Toda operação que debita/credita precisa do lock pessimista na carteira;
  duas transações concorrentes na mesma carteira serializam, nunca calculam
  saldo com base em leitura desatualizada (evita a race clássica de
  "saldo negativo por corrida").
- Se `balance` e o ledger algum dia divergirem (bug, migração manual), o
  ledger é a referência para reprocessar/corrigir o valor materializado —
  nunca o inverso.

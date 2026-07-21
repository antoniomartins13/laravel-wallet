# ADR-0012 — Ledger imutável (append-only)

- **Status**: aceito
- **Data**: 2026-07-20

## Contexto

`transactions` é o registro histórico de tudo que aconteceu com o dinheiro de
um usuário. Um sistema financeiro precisa reconstruir "o que aconteceu e
quando" a qualquer momento, para auditoria e para resolver disputas
("por que meu saldo é esse?").

## Decisão

Nenhuma linha de `transactions` é atualizada ou apagada após criada (sem
`UPDATE`/`DELETE` em valores de negócio). Reverter uma transação não altera o
registro original: cria uma nova transação do tipo `reversal`, apontando para
a original via `reference_id` (com índice `unique`, garantindo no máximo uma
reversão por transação).

## Alternativas consideradas

- **Editar `status` da transação original para `reversed`** — mais simples,
  mas destrói a informação de que uma reversão efetivamente ocorreu como
  evento distinto no tempo, e complica reconciliar o saldo (a soma das linhas
  deixaria de bater com o histórico real de eventos).
- **Soft delete (`deleted_at`)** — sinaliza remoção lógica, mas o modelo
  correto aqui não é "a transação deixou de existir", é "uma transação nova
  cancela o efeito da anterior". Soft delete comunica a semântica errada.

## Consequências

- O histórico de qualquer carteira é auditável e reconstruível: nada
  desaparece, nada muda de valor depois de gravado.
- A regra "no máximo uma reversão por transação original" é garantida por
  constraint de banco (`uq_single_reversal`), não apenas por lógica de
  aplicação.
- Corrigir um erro sempre significa uma nova transação compensatória, nunca
  um `UPDATE` — inclusive em código de manutenção/suporte.

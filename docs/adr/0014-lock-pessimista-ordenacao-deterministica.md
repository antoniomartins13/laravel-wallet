# ADR-0014 — Lock pessimista com ordenação determinística em transferências

- **Status**: aceito
- **Data**: 2026-07-21

## Contexto

Uma transferência lê e escreve o saldo de duas carteiras (origem e destino)
dentro da mesma operação. Duas transferências concorrentes em sentidos
opostos entre as mesmas duas carteiras (A→B e B→A ao mesmo tempo) podem
travar uma na outra se cada uma já segura o lock da carteira que a outra
está esperando — um deadlock clássico.

## Decisão

`WalletRepository::lockManyForUpdate()` busca as duas carteiras numa única
query, ordenada por `id`, com `lockForUpdate()`:

```php
Wallet::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();
```

Como a ordenação é sempre pelo mesmo critério (UUID), duas transferências
concorrentes — não importa a direção — sempre tentam adquirir os locks na
mesma ordem relativa. Uma delas sempre consegue os dois locks primeiro; a
outra espera, nunca há espera circular. `TransferService` e `ReversalService`
reutilizam o mesmo método.

## Alternativas consideradas

- **Lock em duas queries separadas** (`findByIdForUpdate` duas vezes, uma
  por carteira) — mais simples de ler, mas a ordem de aquisição fica
  implícita na ordem dos argumentos passados pelo chamador. Um Service que
  esqueça de ordenar (ex.: sempre `from` antes de `to`) reintroduz o
  deadlock exatamente no cenário de transferências em sentidos opostos.
- **Lock em nível de aplicação (mutex/Redis lock)** — resolve o mesmo
  problema, mas adiciona uma dependência de infraestrutura (Redis) só para
  um problema que o próprio banco já resolve com `SELECT ... FOR UPDATE`
  bem ordenado.

## Consequências

- Nenhum Service precisa pensar em ordem de lock — `lockManyForUpdate`
  garante isso centralizadamente.
- Se uma nova operação multi-carteira for adicionada no futuro (ex.: taxa
  cobrada de uma terceira carteira), ela deve usar o mesmo método, não
  `findByIdForUpdate` em loop.
- O custo é uma query ligeiramente mais cara (`whereIn` + `orderBy`) contra
  duas queries simples — irrelevante na escala deste projeto, comparado ao
  risco de deadlock que evita.

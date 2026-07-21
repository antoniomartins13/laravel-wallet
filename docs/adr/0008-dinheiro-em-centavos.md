# ADR-0008 — Dinheiro representado em inteiros (centavos)

- **Status**: aceito
- **Data**: 2026-07-20

## Contexto

Toda a aplicação manipula valores monetários (depósitos, transferências,
estornos, saldo). Aritmética financeira exige que somas e subtrações sejam
exatas — não há espaço para erro de arredondamento.

## Decisão

Todo valor monetário é armazenado e manipulado como inteiro representando
centavos (`BIGINT` nas colunas `wallets.balance` e `transactions.amount`).
R$ 10,00 é persistido e transitado internamente como `1000`. A conversão para
`R$ 10,00` (formatação com separador decimal) acontece apenas na borda —
serialização de resposta da API e exibição no frontend.

## Alternativas consideradas

- **`FLOAT`/`DOUBLE`** — representação binária de ponto flutuante não
  representa exatamente frações decimais (`0.1 + 0.2 != 0.3`); inaceitável
  para saldo de carteira.
- **`DECIMAL(N,2)`** — correto matematicamente, mas exige que toda a
  aritmética passe por bibliotecas de precisão arbitrária em PHP (BC Math)
  para não reintroduzir erro na camada de aplicação. Centavos como inteiro
  aproveita aritmética nativa de inteiros, mais simples e igualmente exata.

## Consequências

- Soma/subtração de saldo é aritmética de inteiro, sem erro de precisão.
- Toda entrada de valor (request da API) precisa ser convertida para centavos
  na borda antes de chegar à camada de domínio; todo valor de saída precisa
  ser formatado de volta para reais na resposta/UI.
- Enums e validações de `amount > 0` (ADR-0011) operam sobre esse inteiro.

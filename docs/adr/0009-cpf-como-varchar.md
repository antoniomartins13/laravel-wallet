# ADR-0009 — CPF armazenado como VARCHAR

- **Status**: aceito
- **Data**: 2026-07-20

## Contexto

`users.cpf` identifica unicamente uma pessoa no sistema e é usado em
validação de cadastro. CPFs válidos frequentemente começam com `0`
(ex.: `012.345.678-90`).

## Decisão

`cpf` é armazenado como `VARCHAR(11)` (apenas dígitos, sem máscara), com
validação de formato/dígitos verificadores na camada de aplicação e um
índice `unique` na coluna.

## Alternativas consideradas

- **Coluna inteira (`BIGINT`)** — descartada: um CPF como
  `01234567890` seria normalizado para `1234567890` ao ser convertido para
  inteiro, perdendo o zero à esquerda e corrompendo o dado — dois CPFs
  distintos poderiam colidir ou um CPF válido se tornaria irrecuperável.
- **Armazenar com máscara (`000.000.000-00`)** — evitada: mistura formatação
  de apresentação com dado persistido, complica o índice `unique` (duas
  entradas do mesmo CPF em formatos diferentes escapam da constraint) e a
  busca por igualdade.

## Consequências

- CPF é tratado como string em toda a aplicação; nunca sofre operação
  aritmética.
- Índice `unique` garante um único usuário por CPF no banco, além da
  validação de formato na aplicação.
- Máscara de exibição (`000.000.000-00`) é responsabilidade exclusiva do
  frontend.

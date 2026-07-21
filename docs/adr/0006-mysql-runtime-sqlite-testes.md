# ADR-0006 — MySQL em execução, SQLite em memória nos testes

- **Status**: aceito
- **Data**: 2026-07-20

## Contexto

O desafio pede SQL e a aplicação usará locks pessimistas e transações. A suíte
de testes (TDD) precisa ser rápida o suficiente para rodar a cada commit e no
CI de cada PR.

## Decisão

- **MySQL 8** como banco de desenvolvimento e produção (via Docker).
- **SQLite `:memory:`** exclusivamente na suíte de testes (local e CI),
  configurado no `phpunit.xml`.

## Alternativas consideradas

- **MySQL também nos testes** — máxima fidelidade de dialeto, porém suíte
  ordens de magnitude mais lenta e CI mais complexo (serviço de banco no
  runner). Custo alto para um ganho que não afeta as regras de negócio.
- **SQLite em tudo** — inadequado para o runtime: concorrência limitada e
  semântica de `SELECT ... FOR UPDATE` incompatível com o design de locks.

## Consequências

- Suíte de testes em segundos; CI barato e determinístico.
- Compromisso assumido: diferenças de dialeto entre SQLite e MySQL não são
  cobertas pelos testes. Mitigação: uso exclusivo de Eloquent/Query Builder
  (sem SQL cru específico de dialeto); em um projeto maior, um job periódico
  do CI rodaria a suíte contra MySQL real.
- Migrations devem permanecer portáveis entre os dois bancos.

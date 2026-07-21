# ADR-0001 — Registrar decisões arquiteturais em ADRs

- **Status**: aceito
- **Data**: 2026-07-20

## Contexto

O desafio avalia explicitamente "saber argumentar suas escolhas" e "domínio da
arquitetura". Decisões tomadas apenas na cabeça (ou em mensagens de commit) se
perdem e são difíceis de defender depois.

## Decisão

Toda decisão arquitetural relevante será registrada em um ADR curto neste
diretório (`docs/adr/`), numerado sequencialmente por ordem de criação, escrito
no momento em que a decisão é tomada.

## Consequências

- O repositório documenta o raciocínio, não só o resultado.
- ADRs nunca são editados para "mudar de ideia": uma decisão revertida gera um
  novo ADR que supersede o antigo, preservando o histórico.
- Custo pequeno e contínuo de escrita a cada decisão.

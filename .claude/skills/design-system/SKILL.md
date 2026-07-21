---
name: design-system
description: Identidade visual da carteira financeira (estilo private banking, referência BTG Pactual). Use esta skill SEMPRE que criar ou alterar qualquer componente React, página, layout, estilo Tailwind, tela de autenticação, dashboard, modal, formulário, toast, ou qualquer coisa visível ao usuário — mesmo que o pedido não mencione "design". Contém a paleta oficial, tokens Tailwind, regras de composição, receitas de componentes e anti-padrões proibidos.
---

# Design System — Carteira Financeira

Persona de design: banco de investimento premium brasileiro. Sobriedade,
confiança e precisão. O usuário está lidando com dinheiro — a interface deve
transmitir solidez, nunca euforia.

## Paleta (única fonte de verdade)

| Token Tailwind | Hex | Papel |
|---|---|---|
| `primary` (700) | `#01255E` | Navy — headers, sidebar, botões primários, títulos |
| `primary-900` | `#011538` | Navy profundo — hovers do navy, fundos de destaque |
| `primary-100` | `#C5D1E4` | Navy claro — bordas ativas, fundos sutis de seleção |
| `primary-50` | `#E8EDF5` | Lavagem de navy — hover de linhas, chips |
| `gold` | `#FFD700` | Dourado — APENAS acentos (ver regras abaixo) |
| `gold-600` | `#D4AF37` | Dourado envelhecido — hover/estado ativo do dourado |
| `surface` | `#F4F4F4` | Fundo de página |
| `ink` | `#1F1F1E` | Texto principal |
| branco puro | `#FFFFFF` | EXCLUSIVO para cards/superfícies elevadas |

Semânticas (únicas cores fora da paleta, usar com moderação):
verde `#15803D` para crédito/sucesso, vermelho `#B91C1C` para débito/erro.

### Regras do dourado (a alma do design — violar isso destrói a estética)

- Dourado aparece em NO MÁXIMO um elemento por tela: o CTA principal, OU um
  filete de acento (borda de 2-3px), OU um ícone de destaque.
- NUNCA como fundo de área grande, NUNCA em texto corrido, NUNCA em dois
  botões da mesma tela.
- Texto sobre dourado é sempre `primary-900` (nunca branco — contraste ruim).

## Tipografia

- Família única: **Inter** (via fontsource), `antialiased`.
- Títulos: `font-semibold tracking-tight text-primary`.
- Valores monetários: `font-bold tabular-nums` — SEMPRE `tabular-nums` para
  números alinharem em tabelas e não "dançarem" ao atualizar.
- Corpo: `text-ink`; secundário: `text-ink/60`. Nunca cinza fora da escala.

## Composição

- Fundo de página `surface`; conteúdo em cards `bg-white rounded-xl` com
  `shadow-sm` e `border border-black/5`. A hierarquia vem do contraste
  surface × branco, não de sombras pesadas.
- Header/sidebar em `primary` com texto branco; item ativo marcado com
  filete `gold` à esquerda (este é o acento dourado da tela).
- Espaçamento generoso: seções `p-6`+; nunca comprimir para caber.
- Cantos `rounded-xl` (cards) e `rounded-lg` (inputs/botões). Nada de
  `rounded-full` exceto avatares.
- Transições discretas: `transition-colors duration-150`. Sem animações
  chamativas, sem parallax, sem gradientes.

## Receitas de componentes

**Botão primário**: `bg-primary text-white hover:bg-primary-900` +
`focus-visible:ring-2 ring-primary/40`. Variante CTA de destaque (máx. 1 por
tela): `bg-gold text-primary-900 hover:bg-gold-600 font-semibold`.

**Botão secundário**: `border border-primary/20 text-primary
hover:bg-primary-50 bg-transparent`.

**Input**: `bg-white border border-black/10 rounded-lg px-4 py-2.5
focus:border-primary focus:ring-2 ring-primary/20`. Label `text-sm
font-medium text-ink/80` acima; erro em `text-red-700 text-sm` abaixo.

**Card de saldo (assinatura visual do produto)**: fundo `primary`, valor em
branco `text-4xl font-bold tabular-nums`, label `text-white/60 text-sm
uppercase tracking-wider`, botão olho para ocultar saldo (mostra `••••••`),
filete `gold` no topo do card.

**Linha de extrato**: ícone por tipo em círculo `primary-50`, descrição +
data, valor à direita — crédito `text-green-700` prefixo `+`, débito
`text-red-700` prefixo `−`, ambos `tabular-nums`.

**Confirmação de transferência**: sempre uma etapa de revisão (destinatário,
valor, data) antes do envio — padrão de banco real. Botão de confirmar é o
CTA dourado.

## Voz e microcopy

- Português, sentence case, verbos ativos: "Transferir", "Depositar",
  "Confirmar transferência". Nunca "Enviar" genérico ou "Submeter".
- Erros dizem o que aconteceu e o que fazer: "Saldo insuficiente para esta
  transferência." — sem pedir desculpas, sem vagueza.
- Estados vazios convidam à ação: "Você ainda não tem movimentações. Faça seu
  primeiro depósito."
- Valores sempre `R$ 1.234,56` (pt-BR). Use o helper `formatCents`.

## Anti-padrões (proibidos)

- Emojis na UI. Gradientes. Dourado em área grande ou em 2+ elementos.
- Cores fora da paleta (incluindo cinzas do Tailwind — use `ink` com
  opacidade e `surface`).
- Branco puro como fundo de página (reservado a cards).
- Dark mode: fora de escopo, não implementar.
- Bibliotecas de UI prontas (shadcn, MUI, DaisyUI): componentes são próprios,
  construídos com Tailwind puro.

## Acessibilidade (piso de qualidade, sem anunciar)

Foco visível em tudo que é interativo (`focus-visible:ring-2`), inputs sempre
com label, contraste mínimo AA (o navy sobre branco e o ink sobre surface já
passam), áreas de toque ≥ 40px no mobile, responsivo até 375px.

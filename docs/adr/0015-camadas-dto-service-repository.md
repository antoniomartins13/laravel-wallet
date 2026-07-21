# ADR-0015 — Camadas DTO → Service → Repository (interface)

- **Status**: aceito
- **Data**: 2026-07-21

## Contexto

Toda operação financeira (depósito, transferência, reversão) segue o mesmo
fluxo: `FormRequest` valida e monta um DTO → `Controller` fino chama um
`Service` → `Service` orquestra regra de negócio e transação de banco →
acesso a dados passa por um `Repository` atrás de uma interface, não
Eloquent direto. Vale registrar por que cada camada existe, porque cada uma
tem um custo de indireção que precisa se pagar.

## Decisão

- **DTO** (`App\DTOs\*`) — dados tipados e imutáveis (`readonly`) que
  atravessam as camadas. Existe pra o Service nunca depender do shape de um
  `Request` HTTP; um Service pode ser chamado de um job, um comando artisan
  ou um teste sem precisar simular uma requisição.
- **Service** (`App\Services\*`) — dona da regra de negócio e da
  `DB::transaction()`. Um Controller nunca abre transação nem decide;
  só orquestra.
- **Repository via interface** (`App\Repositories\Contracts\*`) — Services
  dependem de `WalletRepositoryInterface`/`TransactionRepositoryInterface`,
  nunca de `Wallet`/`Transaction` diretamente. Bind concreto em
  `RepositoryServiceProvider`.

## Alternativas consideradas

- **Controller chamando Eloquent direto** — menos código, mas mistura
  validação/orquestração HTTP com regra de negócio; testar a regra exige
  sempre montar uma requisição HTTP completa.
- **Service sem Repository (Eloquent direto no Service)** — a alternativa
  mais discutida aqui. O Eloquent já é um Active Record razoavelmente
  testável sozinho; a interface é uma camada de indireção que, num projeto
  deste tamanho, poderia ser vista como complexidade de currículo. A
  justificativa que decidiu a favor da interface: testabilidade real, não
  hipotética — `DepositServiceTest` (unit, `tests/Unit/Services/`) mocka
  `WalletRepositoryInterface`/`TransactionRepositoryInterface` e testa a
  orquestração do `DepositService` sem tocar banco, migration ou Eloquent
  algum. Sem a interface, esse teste não seria possível sem uma
  `RefreshDatabase` completa. Essa é a régua usada: só existe camada de
  indireção onde há um teste que a explora de verdade.

## Consequências

- Cada Service é testável em isolamento via mock das interfaces — usado
  ativamente em `DepositServiceTest`, não é capacidade não exercida.
- Uma segunda implementação de repositório (ex.: cache, outra fonte de
  dados) é troca de binding no provider, sem tocar Service.
- Custo aceito: mais um arquivo por agregado (`Contracts/*Interface.php` +
  implementação), e um nível a mais para navegar ao seguir o fluxo de uma
  operação. Considerado discutível em projeto pequeno — aqui, o objetivo
  explícito é demonstrar domínio de arquitetura em camadas.

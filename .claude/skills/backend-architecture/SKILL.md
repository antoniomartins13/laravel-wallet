---
name: backend-architecture
description: Arquitetura em camadas e regras de domínio do backend Laravel da carteira financeira. Use esta skill SEMPRE que criar ou alterar qualquer código PHP — controller, service, repository, DTO, model, migration, exception, middleware, request, teste PHPUnit, rota ou seeder — mesmo que o pedido pareça simples. Define onde cada tipo de código vive, o fluxo entre camadas, o padrão de dinheiro/UUID/ledger e as convenções de teste TDD.
---

# Arquitetura Backend — Carteira Financeira

Fluxo obrigatório de toda feature:

```
Rota → Middleware → FormRequest → Controller → DTO → Service → Repository → Model
                                                  ↘ Exception de domínio → Handler → JSON
```

Nenhuma camada pula outra: Controller nunca toca Model; Service nunca lê
Request; Repository nunca contém regra de negócio.

## Responsabilidade de cada camada

**Controller** (`app/Http/Controllers`) — fino. Recebe o FormRequest, monta o
DTO, chama UM método de UM service, devolve Resource. Sem `if` de negócio.

```php
public function store(TransferRequest $request): JsonResponse
{
    $transaction = $this->transferService->execute(
        TransferDTO::fromRequest($request)
    );
    return TransactionResource::make($transaction)
        ->response()->setStatusCode(201);
}
```

**FormRequest** (`app/Http/Requests`) — validação de FORMA (tipos, presença,
formato de CPF, valor > 0). Regras de NEGÓCIO (saldo suficiente, carteira
existe) ficam no Service. `authorize()` usa Policies quando aplicável.

**DTO** (`app/DTOs`) — `final readonly class`, propriedades tipadas,
construtor + factory `fromRequest()`. Dinheiro chega aqui já como `int`
centavos. DTOs não têm comportamento além de construção.

```php
final readonly class TransferDTO
{
    public function __construct(
        public string $fromWalletId,
        public string $toWalletId,
        public int $amountInCents,
    ) {}
}
```

**Service** (`app/Services`) — TODO o negócio. Uma classe por caso de uso
(`DepositService`, `TransferService`, `ReversalService`, `StatementService`),
um método público `execute()`. Movimentação de saldo SEMPRE neste padrão:

```php
return DB::transaction(function () use ($dto) {
    $wallets = $this->walletRepository
        ->findManyForUpdate([$dto->fromWalletId, $dto->toWalletId]);
        // internamente: whereIn(...)->orderBy('id')->lockForUpdate()
        // ordem determinística por UUID = sem deadlock em A→B / B→A

    // 1. validar invariantes (lançar exception de domínio se violar)
    // 2. debitar / creditar (update do balance)
    // 3. gravar registros no ledger (par transfer_out + transfer_in)
});
```

**Repository** (`app/Repositories` + `Contracts/`) — só acesso a dados.
Sempre par interface + implementação, binding no
`RepositoryServiceProvider`. Services recebem a INTERFACE no construtor
(DIP). Métodos nomeados pela intenção: `findForUpdate`, `sumBalanceByWallet`
— nunca `query()` genérico vazando builder.

**Model** (`app/Models`) — `HasUuids`, `$fillable` explícito, casts
(`type` → enum, `amount` → int, `metadata` → array), relationships. Sem
lógica de negócio. `Transaction` NÃO tem mutators de update: ledger imutável.

**Exceptions** (`app/Exceptions`) — uma por violação de invariante:
`InsufficientBalanceException`, `SelfTransferException`,
`TransactionAlreadyReversedException`, `WalletNotFoundException`. Cada uma
declara seu status HTTP e um `code` estável; o handler global converte em:

```json
{ "message": "Saldo insuficiente para esta transferência.", "code": "INSUFFICIENT_BALANCE" }
```

Nunca vazar stack trace; 500 inesperado é logado com contexto e devolve
mensagem genérica.

**Enums** (`app/Enums`) — backed enums de string:
`TransactionType: deposit | transfer_in | transfer_out | reversal`,
`TransactionStatus: pending | completed | reversed | failed`.

## Invariantes do domínio (nunca violar, sempre testar)

1. Dinheiro é `int` centavos em todas as camadas. Float é bug.
2. `transactions.amount` é sempre positivo; o `type` define o sinal.
3. `transfer_out` e `transfer_in` nascem juntas, na mesma DB transaction.
4. Reversão: nova(s) transação(ões) `reversal` com `reference_id` apontando
   para a original; original muda apenas `status → reversed`. Unique index em
   `reference_id` garante reversão única (Service lança
   `TransactionAlreadyReversedException` antes, para mensagem amigável).
5. Transferência exige `balance >= amount` (saldo negativo bloqueia envio).
6. Depósito soma ao saldo mesmo negativo (requisito do desafio) — sem
   validação de saldo no depósito.
7. Usuário só opera a própria carteira (Policy); só participantes revertem.

## Testes (TDD obrigatório)

- Teste ANTES da implementação. Feature tests em `tests/Feature` por caso de
  uso (`DepositTest`, `TransferTest`, `ReversalTest`); unit tests para
  helpers puros.
- SQLite `:memory:` (já no `phpunit.xml`) + `RefreshDatabase`.
- Factories com states expressivos: `Wallet::factory()->withBalance(10_000)`,
  `->negativeBalance()`.
- Todo cenário de erro tem teste: status HTTP, `code` do JSON e efeito nulo
  no banco (rollback comprovado com asserts de saldo inalterado).
- Cenários obrigatórios do desafio: depósito com saldo negativo acresce ao
  valor; reversão pode deixar saldo negativo; rollback total em falha no meio
  da transferência.
- Nomes descritivos: `test_deposit_on_negative_balance_adds_to_the_value`.

## Rotas

REST em `routes/api.php`, autenticadas com `auth:sanctum`, escrita com
`throttle`. Recursos: `POST /api/deposits`, `POST /api/transfers`,
`POST /api/transactions/{transaction}/reversal`, `GET /api/transactions`,
`GET /api/wallet`.

## Ao criar features novas

1. Ler `docs/database/schema.dbml` antes de mexer em schema; atualizar no
   mesmo PR se mudar.
2. Se a mudança envolver decisão arquitetural, criar ADR em `docs/adr/`.
3. Branch `feat/<escopo>`, commits `feat(<escopo>): ...`, PR para main.

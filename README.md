# laravel-wallet
Carteira digital construído com Laravel. Focado em transações financeiras seguras e arquitetura limpa.

## 1. Backend

### 1.1. Estrutura de pastas
app/
├── Console/
├── DTOs/
│   ├── DepositDTO.php               (readonly class)
│   ├── TransferDTO.php
│   └── ReversalDTO.php
├── Enums/
│   ├── TransactionType.php
│   └── TransactionStatus.php
├── Exceptions/
│   ├── InsufficientBalanceException.php
│   ├── SelfTransferException.php
│   ├── TransactionAlreadyReversedException.php
│   └── WalletNotFoundException.php
├── Http/
│   ├── Controllers/
│   │   ├── Auth/ (Breeze)
│   │   ├── DepositController.php
│   │   ├── TransferController.php
│   │   ├── ReversalController.php
│   │   └── StatementController.php
│   ├── Middleware/
│   │   └── EnsureWalletExists.php
│   ├── Requests/
│   │   ├── DepositRequest.php
│   │   ├── TransferRequest.php
│   │   └── ReversalRequest.php
│   └── Resources/
│       ├── TransactionResource.php
│       └── WalletResource.php
├── Models/
│   ├── User.php
│   ├── Wallet.php
│   └── Transaction.php
├── Providers/
│   └── RepositoryServiceProvider.php   (bind interface → implementação)
├── Repositories/
│   ├── Contracts/
│   │   ├── WalletRepositoryInterface.php
│   │   └── TransactionRepositoryInterface.php
│   ├── WalletRepository.php
│   └── TransactionRepository.php
└── Services/
    ├── DepositService.php
    ├── TransferService.php
    ├── ReversalService.php
    └── StatementService.php

### 1.2. Fluxo de uma requisição
Request HTTP
  → Middleware (auth:sanctum, EnsureWalletExists)
  → FormRequest (validação de entrada + autorização)
  → Controller (fino: orquestra, não decide)
  → DTO (dados tipados, imutáveis, atravessam camadas)
  → Service (regras de negócio + DB Transaction + lock)
  → Repository (acesso a dados via contrato/interface)
  → Model (Eloquent)
  → Resource (serialização da resposta)


## 2. Frontend
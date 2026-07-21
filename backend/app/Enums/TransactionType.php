<?php

namespace App\Enums;

enum TransactionType: string
{
    case Deposit = 'deposit';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case Reversal = 'reversal';
}

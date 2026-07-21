<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Reversed = 'reversed';
    case Failed = 'failed';
}

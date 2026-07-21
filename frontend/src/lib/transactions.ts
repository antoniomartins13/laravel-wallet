import { ArrowDownCircle, ArrowDownLeft, ArrowUpRight, Undo2, type LucideIcon } from 'lucide-react';
import type { TransactionType } from '../types/api';

export interface TransactionDisplay {
  icon: LucideIcon;
  label: string;
  sign: '+' | '−' | '';
  amountColorClass: string;
}

const displayByType: Record<TransactionType, TransactionDisplay> = {
  deposit: {
    icon: ArrowDownCircle,
    label: 'Depósito',
    sign: '+',
    amountColorClass: 'text-green-700',
  },
  transfer_in: {
    icon: ArrowDownLeft,
    label: 'Transferência recebida',
    sign: '+',
    amountColorClass: 'text-green-700',
  },
  transfer_out: {
    icon: ArrowUpRight,
    label: 'Transferência enviada',
    sign: '−',
    amountColorClass: 'text-red-700',
  },
  // A reversal's direction depends on what it reverses (deposit/transfer_in
  // reversals debit, transfer_out reversals credit) — that's not exposed by
  // `type` alone, so it's shown neutrally rather than guessing a sign.
  reversal: {
    icon: Undo2,
    label: 'Reversão',
    sign: '',
    amountColorClass: 'text-ink',
  },
};

export function getTransactionDisplay(type: TransactionType): TransactionDisplay {
  return displayByType[type];
}

import { getTransactionDisplay } from '../../lib/transactions';
import { formatCents } from '../../lib/money';
import type { Transaction } from '../../types/api';
import { Button } from '../ui/Button';

interface TransactionRowProps {
  transaction: Transaction;
  onReverse?: (transaction: Transaction) => void;
  isReversing?: boolean;
}

export function TransactionRow({ transaction, onReverse, isReversing = false }: TransactionRowProps) {
  const display = getTransactionDisplay(transaction.type);
  const Icon = display.icon;
  const canReverse = Boolean(onReverse) && transaction.type !== 'reversal' && !transaction.is_reversed;

  const date = new Date(transaction.created_at).toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });

  return (
    <div className="flex flex-col gap-3 border-b border-black/5 py-3 last:border-0 sm:flex-row sm:items-center sm:justify-between">
      <div className="flex items-center gap-3">
        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary">
          <Icon className="h-5 w-5" aria-hidden="true" />
        </span>
        <div>
          <p className="text-sm font-medium text-ink">{display.label}</p>
          <p className="text-xs text-ink/50">{date}</p>
          {transaction.is_reversed && (
            <span className="mt-0.5 inline-block rounded-full bg-primary-50 px-2 py-0.5 text-[11px] font-medium text-primary">
              Revertida
            </span>
          )}
        </div>
      </div>

      <div className="flex items-center justify-between gap-4 sm:flex-col sm:items-end sm:justify-center">
        <p className={`font-bold tabular-nums ${display.amountColorClass}`}>
          {display.sign}
          {formatCents(transaction.amount)}
        </p>
        {canReverse && (
          <Button
            type="button"
            variant="secondary"
            className="px-3 py-1.5 text-xs"
            isLoading={isReversing}
            onClick={() => onReverse?.(transaction)}
          >
            Reverter
          </Button>
        )}
      </div>
    </div>
  );
}

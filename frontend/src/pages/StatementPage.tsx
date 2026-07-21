import { useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Card } from '../components/ui/Card';
import { EmptyState } from '../components/ui/EmptyState';
import { Spinner } from '../components/ui/Spinner';
import { Button } from '../components/ui/Button';
import { TransactionRow } from '../components/wallet/TransactionRow';
import { useStatement } from '../hooks/useStatement';
import { useReversal } from '../hooks/useReversal';
import { useToast } from '../hooks/useToast';
import { getApiErrorMessage } from '../lib/errors';
import type { Transaction } from '../types/api';

export function StatementPage() {
  const [page, setPage] = useState(1);
  const { data: statement, isLoading } = useStatement(page, 15);
  const reversalMutation = useReversal();
  const { showToast } = useToast();
  const [reversingId, setReversingId] = useState<string | null>(null);

  const handleReverse = async (transaction: Transaction) => {
    setReversingId(transaction.id);

    try {
      await reversalMutation.mutateAsync(transaction.id);
      showToast('success', 'Transação revertida com sucesso.');
    } catch (error) {
      showToast('error', getApiErrorMessage(error, 'Não foi possível reverter esta transação.'));
    } finally {
      setReversingId(null);
    }
  };

  return (
    <div className="mx-auto max-w-3xl">
      <h1 className="text-2xl font-semibold tracking-tight text-primary">Extrato</h1>
      <p className="mt-1 text-sm text-ink/60">Todas as suas movimentações, mais recentes primeiro.</p>

      <Card className="mt-6 p-6">
        {isLoading ? (
          <div className="flex justify-center py-12">
            <Spinner />
          </div>
        ) : statement && statement.data.length > 0 ? (
          <>
            <div>
              {statement.data.map((transaction) => (
                <TransactionRow
                  key={transaction.id}
                  transaction={transaction}
                  onReverse={handleReverse}
                  isReversing={reversingId === transaction.id}
                />
              ))}
            </div>

            {statement.meta.last_page > 1 && (
              <div className="mt-4 flex items-center justify-between border-t border-black/5 pt-4">
                <Button
                  type="button"
                  variant="ghost"
                  disabled={page <= 1}
                  onClick={() => setPage((current) => current - 1)}
                  className="px-3"
                >
                  <ChevronLeft className="h-4 w-4" aria-hidden="true" />
                  Anterior
                </Button>
                <p className="text-sm text-ink/60">
                  Página {statement.meta.current_page} de {statement.meta.last_page}
                </p>
                <Button
                  type="button"
                  variant="ghost"
                  disabled={page >= statement.meta.last_page}
                  onClick={() => setPage((current) => current + 1)}
                  className="px-3"
                >
                  Próxima
                  <ChevronRight className="h-4 w-4" aria-hidden="true" />
                </Button>
              </div>
            )}
          </>
        ) : (
          <EmptyState title="Você ainda não tem movimentações." description="Faça seu primeiro depósito." />
        )}
      </Card>
    </div>
  );
}

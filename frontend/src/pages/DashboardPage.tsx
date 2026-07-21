import { Link } from 'react-router-dom';
import { ArrowLeftRight, PlusCircle } from 'lucide-react';
import { BalanceCard } from '../components/wallet/BalanceCard';
import { TransactionRow } from '../components/wallet/TransactionRow';
import { Card } from '../components/ui/Card';
import { EmptyState } from '../components/ui/EmptyState';
import { Spinner } from '../components/ui/Spinner';
import { useWallet } from '../hooks/useWallet';
import { useStatement } from '../hooks/useStatement';

export function DashboardPage() {
  const { data: wallet, isLoading: isWalletLoading } = useWallet();
  const { data: statement, isLoading: isStatementLoading } = useStatement(1, 5);

  return (
    <div className="mx-auto flex max-w-3xl flex-col gap-6">
      <BalanceCard balance={wallet?.balance ?? 0} isLoading={isWalletLoading} />

      <div className="grid grid-cols-2 gap-4">
        <Link
          to="/deposit"
          className="flex items-center justify-center gap-2 rounded-lg border border-primary/20 bg-white px-4 py-3 text-sm font-medium text-primary shadow-sm transition-colors duration-150 hover:bg-primary-50"
        >
          <PlusCircle className="h-5 w-5" aria-hidden="true" />
          Depositar
        </Link>
        <Link
          to="/transfer"
          className="flex items-center justify-center gap-2 rounded-lg border border-primary/20 bg-white px-4 py-3 text-sm font-medium text-primary shadow-sm transition-colors duration-150 hover:bg-primary-50"
        >
          <ArrowLeftRight className="h-5 w-5" aria-hidden="true" />
          Transferir
        </Link>
      </div>

      <Card className="p-6">
        <div className="mb-2 flex items-center justify-between">
          <h2 className="font-semibold text-primary">Últimas movimentações</h2>
          <Link to="/statement" className="text-sm font-medium text-primary hover:underline">
            Ver extrato
          </Link>
        </div>

        {isStatementLoading ? (
          <div className="flex justify-center py-8">
            <Spinner />
          </div>
        ) : statement && statement.data.length > 0 ? (
          <div>
            {statement.data.map((transaction) => (
              <TransactionRow key={transaction.id} transaction={transaction} />
            ))}
          </div>
        ) : (
          <EmptyState title="Você ainda não tem movimentações." description="Faça seu primeiro depósito." />
        )}
      </Card>
    </div>
  );
}

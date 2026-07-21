import { useState } from 'react';
import { Eye, EyeOff } from 'lucide-react';
import { formatCents } from '../../lib/money';

interface BalanceCardProps {
  balance: number;
  isLoading?: boolean;
}

export function BalanceCard({ balance, isLoading = false }: BalanceCardProps) {
  const [isHidden, setIsHidden] = useState(false);

  return (
    <div className="overflow-hidden rounded-xl bg-primary text-white shadow-sm">
      <div className="h-1 bg-gold" />
      <div className="p-6">
        <div className="flex items-center justify-between">
          <p className="text-sm uppercase tracking-wider text-white/60">Saldo disponível</p>
          <button
            type="button"
            onClick={() => setIsHidden((current) => !current)}
            aria-label={isHidden ? 'Mostrar saldo' : 'Ocultar saldo'}
            className="rounded-lg p-1.5 text-white/60 transition-colors duration-150 hover:bg-white/10 hover:text-white"
          >
            {isHidden ? (
              <EyeOff className="h-5 w-5" aria-hidden="true" />
            ) : (
              <Eye className="h-5 w-5" aria-hidden="true" />
            )}
          </button>
        </div>
        <p className={`mt-2 text-4xl font-bold tabular-nums ${balance < 0 ? 'text-red-400' : 'text-white'}`}>
          {isLoading ? '···' : isHidden ? '••••••' : formatCents(balance)}
        </p>
      </div>
    </div>
  );
}

import { LogOut } from 'lucide-react';
import { useAuth } from '../../hooks/useAuth';

export function UserFooter() {
  const { user, logout } = useAuth();

  return (
    <div className="border-t border-white/10 pt-4">
      <p className="truncate px-3 text-sm font-medium text-white">{user?.name}</p>
      <p className="truncate px-3 text-xs text-white/50">{user?.email}</p>
      <button
        type="button"
        onClick={() => logout()}
        className="mt-3 flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-white/70 transition-colors duration-150 hover:bg-white/5 hover:text-white"
      >
        <LogOut className="h-5 w-5" aria-hidden="true" />
        Sair
      </button>
    </div>
  );
}

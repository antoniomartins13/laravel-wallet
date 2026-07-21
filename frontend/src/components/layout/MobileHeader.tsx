import { LogOut } from 'lucide-react';
import { Logo } from '../Logo';
import { useAuth } from '../../hooks/useAuth';

/** Compact navy top bar shown below the `md` breakpoint. */
export function MobileHeader() {
  const { logout } = useAuth();

  return (
    <header className="flex items-center justify-between bg-primary px-4 py-3 text-white md:hidden">
      <Logo variant="horizontal-white" height={28} />
      <button
        type="button"
        onClick={() => logout()}
        aria-label="Sair"
        className="rounded-lg p-2 text-white/70 transition-colors duration-150 hover:bg-white/10 hover:text-white"
      >
        <LogOut className="h-5 w-5" aria-hidden="true" />
      </button>
    </header>
  );
}

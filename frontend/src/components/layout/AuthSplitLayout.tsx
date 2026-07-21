import type { ReactNode } from 'react';
import { Logo } from '../Logo';

interface AuthSplitLayoutProps {
  title: string;
  subtitle: string;
  children: ReactNode;
}

/** Shared split-screen shell for Login/Register; the navy branding half hides below `md`. */
export function AuthSplitLayout({ title, subtitle, children }: AuthSplitLayoutProps) {
  return (
    <div className="flex min-h-screen bg-surface">
      <div className="hidden w-1/2 flex-col justify-between bg-primary p-10 text-white md:flex">
        <Logo variant="horizontal-white" height={40} />
        <p className="text-2xl font-semibold leading-snug">
          Sua carteira financeira, com a solidez que o seu dinheiro merece.
        </p>
        <p className="text-sm text-white/50">© {new Date().getFullYear()} Carteira Financeira</p>
      </div>

      <div className="flex flex-1 flex-col justify-center px-6 py-12 sm:px-12 md:px-16">
        <div className="mx-auto w-full max-w-sm">
          <Logo variant="mark" height={40} className="mb-8 md:hidden" />
          <h1 className="text-2xl font-semibold tracking-tight text-primary">{title}</h1>
          <p className="mt-1 text-sm text-ink/60">{subtitle}</p>
          <div className="mt-8">{children}</div>
        </div>
      </div>
    </div>
  );
}

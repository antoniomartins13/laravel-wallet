import { Link } from 'react-router-dom';
import { Logo } from '../components/Logo';
import { Button } from '../components/ui/Button';

export function NotFoundPage() {
  return (
    <div className="flex min-h-screen flex-col items-center justify-center gap-4 bg-surface px-6 text-center">
      <Logo variant="mark" height={48} />
      <h1 className="text-2xl font-semibold tracking-tight text-primary">Página não encontrada</h1>
      <p className="max-w-sm text-sm text-ink/60">O endereço que você tentou acessar não existe ou foi movido.</p>
      <Link to="/">
        <Button type="button">Voltar ao início</Button>
      </Link>
    </div>
  );
}

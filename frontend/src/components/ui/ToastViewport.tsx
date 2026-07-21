import { useToast } from '../../hooks/useToast';

export function ToastViewport() {
  const { toasts, dismissToast } = useToast();

  if (toasts.length === 0) {
    return null;
  }

  return (
    <div className="fixed bottom-4 right-4 z-50 flex w-full max-w-sm flex-col gap-2">
      {toasts.map((toast) => (
        <div
          key={toast.id}
          role="status"
          className={`flex items-start justify-between gap-3 rounded-lg border bg-white px-4 py-3 text-sm shadow-sm ${
            toast.variant === 'success' ? 'border-green-700/20 text-green-700' : 'border-red-700/20 text-red-700'
          }`}
        >
          <span>{toast.message}</span>
          <button
            type="button"
            onClick={() => dismissToast(toast.id)}
            className="text-ink/40 transition-colors duration-150 hover:text-ink"
            aria-label="Fechar"
          >
            ×
          </button>
        </div>
      ))}
    </div>
  );
}

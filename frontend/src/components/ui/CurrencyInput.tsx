import { forwardRef } from 'react';
import { formatCents } from '../../lib/money';

interface CurrencyInputProps {
  label: string;
  value: string;
  onChange: (value: string) => void;
  onBlur?: () => void;
  error?: string;
  id?: string;
  name?: string;
  autoFocus?: boolean;
}

/**
 * Digit-accumulating masked currency input: every keystroke is treated as a
 * digit shifting into the cents position (like a real banking app), so the
 * value is always an unambiguous integer number of cents.
 */
export const CurrencyInput = forwardRef<HTMLInputElement, CurrencyInputProps>(function CurrencyInput(
  { label, value, onChange, onBlur, error, id, name, autoFocus },
  ref,
) {
  const cents = value === '' ? 0 : parseInt(value, 10);
  const display = formatCents(cents);
  const inputId = id ?? name;

  return (
    <div className="flex flex-col gap-1.5">
      <label htmlFor={inputId} className="text-sm font-medium text-ink/80">
        {label}
      </label>
      <input
        id={inputId}
        name={name}
        ref={ref}
        inputMode="numeric"
        autoFocus={autoFocus}
        value={display}
        onBlur={onBlur}
        onChange={(event) => {
          const digitsOnly = event.target.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
          onChange(digitsOnly);
        }}
        className={`rounded-lg border bg-white px-4 py-2.5 text-2xl font-bold tabular-nums text-ink focus:outline-none focus:ring-2 ${
          error
            ? 'border-red-700 focus:ring-red-700/20'
            : 'border-black/10 focus:border-primary focus:ring-primary/20'
        }`}
        aria-invalid={Boolean(error)}
        aria-describedby={error ? `${inputId}-error` : undefined}
      />
      {error && (
        <p id={`${inputId}-error`} className="text-sm text-red-700">
          {error}
        </p>
      )}
    </div>
  );
});

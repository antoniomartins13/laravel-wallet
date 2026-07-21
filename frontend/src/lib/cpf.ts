/**
 * Mirrors the backend's App\Rules\ValidCpf check-digit algorithm, so the
 * form fails fast client-side with the same rule the API enforces.
 */
export function isValidCpf(value: string): boolean {
  const cpf = value.replace(/\D/g, '');

  if (cpf.length !== 11) {
    return false;
  }

  if (/^(\d)\1{10}$/.test(cpf)) {
    return false;
  }

  for (let position = 9; position < 11; position++) {
    let sum = 0;

    for (let i = 0; i < position; i++) {
      sum += Number(cpf[i]) * (position + 1 - i);
    }

    const checkDigit = ((10 * sum) % 11) % 10;

    if (Number(cpf[position]) !== checkDigit) {
      return false;
    }
  }

  return true;
}

export function formatCpf(value: string): string {
  const digits = value.replace(/\D/g, '').slice(0, 11);

  return digits
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
}

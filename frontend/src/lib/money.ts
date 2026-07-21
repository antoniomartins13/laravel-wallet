/**
 * Money is handled as integer cents everywhere in this app — API, forms,
 * state. "R$" formatting only ever happens here, at the display boundary.
 */

const currencyFormatter = new Intl.NumberFormat('pt-BR', {
  style: 'currency',
  currency: 'BRL',
});

export function formatCents(cents: number): string {
  return currencyFormatter.format(cents / 100);
}

/**
 * Parses a masked or free-text currency input ("R$ 1.234,56", "1234,56",
 * "123456") into integer cents. Non-digit characters are discarded, so the
 * result always matches what a digit-accumulating masked input produces.
 */
export function parseCurrency(input: string): number {
  const digitsOnly = input.replace(/\D/g, '');

  return digitsOnly === '' ? 0 : parseInt(digitsOnly, 10);
}

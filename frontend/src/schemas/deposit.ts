import { z } from 'zod';
import { parseCurrency } from '../lib/money';

export const depositSchema = z.object({
  amount: z
    .string()
    .min(1, 'Informe o valor do depósito.')
    .transform(parseCurrency)
    .pipe(z.number().min(1, 'O valor deve ser maior que zero.')),
});

export type DepositFormValues = z.input<typeof depositSchema>;
export type DepositFormOutput = z.output<typeof depositSchema>;

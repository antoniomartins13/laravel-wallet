import { z } from 'zod';
import { parseCurrency } from '../lib/money';

export const depositSchema = z.object({
  amount: z
    .string()
    .min(1, 'Informe o valor do depósito.')
    .refine((value) => parseCurrency(value) >= 1, { message: 'O valor deve ser maior que zero.' }),
});

export type DepositFormValues = z.infer<typeof depositSchema>;

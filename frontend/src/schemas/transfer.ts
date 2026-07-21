import { z } from 'zod';
import { parseCurrency } from '../lib/money';

export const recipientSearchSchema = z.object({
  identifier: z.string().min(1, 'Informe o e-mail ou CPF do destinatário.'),
});

export type RecipientSearchFormValues = z.infer<typeof recipientSearchSchema>;

export const transferAmountSchema = z.object({
  amount: z
    .string()
    .min(1, 'Informe o valor da transferência.')
    .refine((value) => parseCurrency(value) >= 1, { message: 'O valor deve ser maior que zero.' }),
});

export type TransferAmountFormValues = z.infer<typeof transferAmountSchema>;

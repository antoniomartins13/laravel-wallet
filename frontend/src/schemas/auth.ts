import { z } from 'zod';
import { isValidCpf } from '../lib/cpf';

export const loginSchema = z.object({
  email: z.string().min(1, 'Informe seu e-mail.').email('E-mail inválido.'),
  password: z.string().min(1, 'Informe sua senha.'),
});

export type LoginFormValues = z.infer<typeof loginSchema>;

export const registerSchema = z
  .object({
    name: z.string().min(1, 'Informe seu nome.').max(255, 'Nome muito longo.'),
    email: z.string().min(1, 'Informe seu e-mail.').email('E-mail inválido.'),
    cpf: z
      .string()
      .min(1, 'Informe seu CPF.')
      .refine(isValidCpf, { message: 'CPF inválido.' }),
    password: z.string().min(8, 'A senha deve ter pelo menos 8 caracteres.'),
    password_confirmation: z.string().min(1, 'Confirme sua senha.'),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: 'As senhas não coincidem.',
    path: ['password_confirmation'],
  });

export type RegisterFormValues = z.infer<typeof registerSchema>;

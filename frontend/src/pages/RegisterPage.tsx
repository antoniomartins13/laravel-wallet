import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { AuthSplitLayout } from '../components/layout/AuthSplitLayout';
import { Input } from '../components/ui/Input';
import { Button } from '../components/ui/Button';
import { useAuth } from '../hooks/useAuth';
import { registerSchema, type RegisterFormValues } from '../schemas/auth';
import { getApiErrorMessage } from '../lib/errors';
import { formatCpf } from '../lib/cpf';

export function RegisterPage() {
  const { register: registerUser } = useAuth();
  const navigate = useNavigate();
  const [formError, setFormError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<RegisterFormValues>({ resolver: zodResolver(registerSchema) });

  const cpfField = register('cpf');

  const onSubmit = async (values: RegisterFormValues) => {
    setFormError(null);

    try {
      await registerUser(values);
      navigate('/', { replace: true });
    } catch (error) {
      setFormError(getApiErrorMessage(error, 'Não foi possível criar sua conta.'));
    }
  };

  return (
    <AuthSplitLayout title="Criar conta" subtitle="Leva menos de um minuto.">
      <form onSubmit={handleSubmit(onSubmit)} className="flex flex-col gap-4" noValidate>
        <Input label="Nome completo" autoComplete="name" error={errors.name?.message} {...register('name')} />
        <Input
          label="E-mail"
          type="email"
          autoComplete="email"
          error={errors.email?.message}
          {...register('email')}
        />
        <Input
          label="CPF"
          inputMode="numeric"
          placeholder="000.000.000-00"
          error={errors.cpf?.message}
          {...cpfField}
          onChange={(event) => {
            event.target.value = formatCpf(event.target.value);
            cpfField.onChange(event);
          }}
        />
        <Input
          label="Senha"
          type="password"
          autoComplete="new-password"
          error={errors.password?.message}
          {...register('password')}
        />
        <Input
          label="Confirmar senha"
          type="password"
          autoComplete="new-password"
          error={errors.password_confirmation?.message}
          {...register('password_confirmation')}
        />

        {formError && <p className="text-sm text-red-700">{formError}</p>}

        <Button type="submit" isLoading={isSubmitting} className="mt-2 w-full">
          Criar conta
        </Button>
      </form>

      <p className="mt-6 text-center text-sm text-ink/60">
        Já tem conta?{' '}
        <Link to="/login" className="font-medium text-primary hover:underline">
          Entrar
        </Link>
      </p>
    </AuthSplitLayout>
  );
}

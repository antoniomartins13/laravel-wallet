import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { AuthSplitLayout } from '../components/layout/AuthSplitLayout';
import { Input } from '../components/ui/Input';
import { Button } from '../components/ui/Button';
import { useAuth } from '../hooks/useAuth';
import { loginSchema, type LoginFormValues } from '../schemas/auth';
import { getApiErrorMessage } from '../lib/errors';

export function LoginPage() {
  const { login } = useAuth();
  const navigate = useNavigate();
  const [formError, setFormError] = useState<string | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<LoginFormValues>({ resolver: zodResolver(loginSchema) });

  const onSubmit = async (values: LoginFormValues) => {
    setFormError(null);

    try {
      await login(values);
      navigate('/', { replace: true });
    } catch (error) {
      setFormError(getApiErrorMessage(error, 'Não foi possível entrar. Verifique seus dados.'));
    }
  };

  return (
    <AuthSplitLayout title="Entrar" subtitle="Acesse sua carteira financeira.">
      <form onSubmit={handleSubmit(onSubmit)} className="flex flex-col gap-4" noValidate>
        <Input
          label="E-mail"
          type="email"
          autoComplete="email"
          error={errors.email?.message}
          {...register('email')}
        />
        <Input
          label="Senha"
          type="password"
          autoComplete="current-password"
          error={errors.password?.message}
          {...register('password')}
        />

        {formError && <p className="text-sm text-red-700">{formError}</p>}

        <Button type="submit" isLoading={isSubmitting} className="mt-2 w-full">
          Entrar
        </Button>
      </form>

      <p className="mt-6 text-center text-sm text-ink/60">
        Ainda não tem conta?{' '}
        <Link to="/register" className="font-medium text-primary hover:underline">
          Criar conta
        </Link>
      </p>
    </AuthSplitLayout>
  );
}

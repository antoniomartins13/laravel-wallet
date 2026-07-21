import { useNavigate } from 'react-router-dom';
import { Controller, useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Card } from '../components/ui/Card';
import { CurrencyInput } from '../components/ui/CurrencyInput';
import { Button } from '../components/ui/Button';
import { useDeposit } from '../hooks/useDeposit';
import { useToast } from '../hooks/useToast';
import { depositSchema, type DepositFormValues } from '../schemas/deposit';
import { getApiErrorMessage } from '../lib/errors';
import { parseCurrency } from '../lib/money';

export function DepositPage() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const depositMutation = useDeposit();

  const {
    control,
    handleSubmit,
    formState: { errors },
  } = useForm<DepositFormValues>({
    resolver: zodResolver(depositSchema),
    defaultValues: { amount: '' },
  });

  const onSubmit = async (values: DepositFormValues) => {
    try {
      await depositMutation.mutateAsync({ amount: parseCurrency(values.amount) });
      showToast('success', 'Depósito realizado com sucesso.');
      navigate('/');
    } catch (error) {
      showToast('error', getApiErrorMessage(error, 'Não foi possível concluir o depósito.'));
    }
  };

  return (
    <div className="mx-auto max-w-md">
      <h1 className="text-2xl font-semibold tracking-tight text-primary">Depositar</h1>
      <p className="mt-1 text-sm text-ink/60">O valor é somado ao seu saldo imediatamente.</p>

      <Card className="mt-6 p-6">
        <form onSubmit={handleSubmit(onSubmit)} className="flex flex-col gap-4" noValidate>
          <Controller
            control={control}
            name="amount"
            render={({ field }) => (
              <CurrencyInput
                label="Valor do depósito"
                value={field.value}
                onChange={field.onChange}
                onBlur={field.onBlur}
                error={errors.amount?.message}
                autoFocus
              />
            )}
          />

          <Button type="submit" isLoading={depositMutation.isPending} className="mt-2 w-full">
            Depositar
          </Button>
        </form>
      </Card>
    </div>
  );
}

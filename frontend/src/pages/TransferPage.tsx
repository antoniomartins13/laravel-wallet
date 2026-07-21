import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Controller, useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { Card } from '../components/ui/Card';
import { Input } from '../components/ui/Input';
import { CurrencyInput } from '../components/ui/CurrencyInput';
import { Button } from '../components/ui/Button';
import { useWalletLookup, type RecipientLookupResult } from '../hooks/useWalletLookup';
import { useTransfer } from '../hooks/useTransfer';
import { useToast } from '../hooks/useToast';
import {
  recipientSearchSchema,
  transferAmountSchema,
  type RecipientSearchFormValues,
  type TransferAmountFormValues,
} from '../schemas/transfer';
import { getApiErrorMessage } from '../lib/errors';
import { formatCents, parseCurrency } from '../lib/money';

type Step = 'search' | 'amount' | 'confirm';

const stepSubtitle: Record<Step, (recipient: RecipientLookupResult | null) => string> = {
  search: () => 'Busque o destinatário por e-mail ou CPF.',
  amount: (recipient) => `Para ${recipient?.name ?? ''}.`,
  confirm: () => 'Revise antes de confirmar.',
};

export function TransferPage() {
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [step, setStep] = useState<Step>('search');
  const [recipient, setRecipient] = useState<RecipientLookupResult | null>(null);
  const [amountCents, setAmountCents] = useState(0);

  const lookupMutation = useWalletLookup();
  const transferMutation = useTransfer();

  const searchForm = useForm<RecipientSearchFormValues>({
    resolver: zodResolver(recipientSearchSchema),
    defaultValues: { identifier: '' },
  });

  const amountForm = useForm<TransferAmountFormValues>({
    resolver: zodResolver(transferAmountSchema),
    defaultValues: { amount: '' },
  });

  const onSearch = async (values: RecipientSearchFormValues) => {
    try {
      const found = await lookupMutation.mutateAsync(values.identifier);
      setRecipient(found);
      setStep('amount');
    } catch (error) {
      searchForm.setError('identifier', {
        message: getApiErrorMessage(error, 'Destinatário não encontrado.'),
      });
    }
  };

  const onAmountSubmit = (values: TransferAmountFormValues) => {
    setAmountCents(parseCurrency(values.amount));
    setStep('confirm');
  };

  const onConfirm = async () => {
    if (!recipient) {
      return;
    }

    try {
      await transferMutation.mutateAsync({ to_wallet_id: recipient.wallet_id, amount: amountCents });
      showToast('success', `Transferência de ${formatCents(amountCents)} para ${recipient.name} concluída.`);
      navigate('/');
    } catch (error) {
      showToast('error', getApiErrorMessage(error, 'Não foi possível concluir a transferência.'));
    }
  };

  return (
    <div className="mx-auto max-w-md">
      <h1 className="text-2xl font-semibold tracking-tight text-primary">Transferir</h1>
      <p className="mt-1 text-sm text-ink/60">{stepSubtitle[step](recipient)}</p>

      <Card className="mt-6 p-6">
        {step === 'search' && (
          <form onSubmit={searchForm.handleSubmit(onSearch)} className="flex flex-col gap-4" noValidate>
            <Input
              label="E-mail ou CPF do destinatário"
              autoFocus
              error={searchForm.formState.errors.identifier?.message}
              {...searchForm.register('identifier')}
            />
            <Button type="submit" isLoading={lookupMutation.isPending} className="w-full">
              Buscar
            </Button>
          </form>
        )}

        {step === 'amount' && recipient && (
          <form onSubmit={amountForm.handleSubmit(onAmountSubmit)} className="flex flex-col gap-4" noValidate>
            <div className="rounded-lg bg-primary-50 px-4 py-3 text-sm text-primary">
              Transferindo para <span className="font-semibold">{recipient.name}</span>
            </div>
            <Controller
              control={amountForm.control}
              name="amount"
              render={({ field }) => (
                <CurrencyInput
                  label="Valor"
                  value={field.value}
                  onChange={field.onChange}
                  onBlur={field.onBlur}
                  error={amountForm.formState.errors.amount?.message}
                  autoFocus
                />
              )}
            />
            <div className="flex gap-3">
              <Button type="button" variant="secondary" onClick={() => setStep('search')} className="flex-1">
                Voltar
              </Button>
              <Button type="submit" className="flex-1">
                Continuar
              </Button>
            </div>
          </form>
        )}

        {step === 'confirm' && recipient && (
          <div className="flex flex-col gap-4">
            <dl className="flex flex-col gap-3 rounded-lg bg-surface p-4 text-sm">
              <div className="flex justify-between">
                <dt className="text-ink/60">Destinatário</dt>
                <dd className="font-medium text-ink">{recipient.name}</dd>
              </div>
              <div className="flex justify-between">
                <dt className="text-ink/60">Valor</dt>
                <dd className="font-bold tabular-nums text-ink">{formatCents(amountCents)}</dd>
              </div>
              <div className="flex justify-between">
                <dt className="text-ink/60">Data</dt>
                <dd className="text-ink">{new Date().toLocaleDateString('pt-BR')}</dd>
              </div>
            </dl>

            <div className="flex gap-3">
              <Button type="button" variant="secondary" onClick={() => setStep('amount')} className="flex-1">
                Voltar
              </Button>
              <Button
                type="button"
                variant="cta"
                isLoading={transferMutation.isPending}
                onClick={onConfirm}
                className="flex-1"
              >
                Confirmar transferência
              </Button>
            </div>
          </div>
        )}
      </Card>
    </div>
  );
}

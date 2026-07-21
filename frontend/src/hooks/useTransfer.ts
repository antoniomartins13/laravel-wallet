import { useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '../lib/api';
import { queryKeys } from '../lib/queryClient';
import type { ApiResource, Transaction } from '../types/api';

interface TransferInput {
  to_wallet_id: string;
  amount: number;
}

async function transfer(input: TransferInput): Promise<Transaction> {
  const { data } = await api.post<ApiResource<Transaction>>('/api/transfers', input);

  return data.data;
}

export function useTransfer() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: transfer,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: queryKeys.wallet });
      queryClient.invalidateQueries({ queryKey: ['statement'] });
    },
  });
}

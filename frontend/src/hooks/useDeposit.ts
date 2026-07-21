import { useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '../lib/api';
import { queryKeys } from '../lib/queryClient';
import type { ApiResource, Transaction } from '../types/api';

interface DepositInput {
  amount: number;
}

async function deposit(input: DepositInput): Promise<Transaction> {
  const { data } = await api.post<ApiResource<Transaction>>('/api/deposits', input);

  return data.data;
}

export function useDeposit() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: deposit,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: queryKeys.wallet });
      queryClient.invalidateQueries({ queryKey: ['statement'] });
    },
  });
}

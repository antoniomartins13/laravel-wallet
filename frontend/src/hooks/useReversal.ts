import { useMutation, useQueryClient } from '@tanstack/react-query';
import { api } from '../lib/api';
import { queryKeys } from '../lib/queryClient';
import type { ApiResource, Transaction } from '../types/api';

async function reverse(transactionId: string): Promise<Transaction> {
  const { data } = await api.post<ApiResource<Transaction>>(`/api/transactions/${transactionId}/reversal`);

  return data.data;
}

export function useReversal() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: reverse,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: queryKeys.wallet });
      queryClient.invalidateQueries({ queryKey: ['statement'] });
    },
  });
}

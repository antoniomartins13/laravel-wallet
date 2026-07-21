import { useQuery, keepPreviousData } from '@tanstack/react-query';
import { api } from '../lib/api';
import { queryKeys } from '../lib/queryClient';
import type { PaginatedResponse, Transaction } from '../types/api';

async function fetchStatement(page: number, perPage: number): Promise<PaginatedResponse<Transaction>> {
  const { data } = await api.get<PaginatedResponse<Transaction>>('/api/transactions', {
    params: { page, per_page: perPage },
  });

  return data;
}

export function useStatement(page: number, perPage = 15) {
  return useQuery({
    queryKey: queryKeys.statement(page, perPage),
    queryFn: () => fetchStatement(page, perPage),
    placeholderData: keepPreviousData,
    refetchInterval: 20_000,
  });
}

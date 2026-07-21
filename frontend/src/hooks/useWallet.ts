import { useQuery } from '@tanstack/react-query';
import { api } from '../lib/api';
import { queryKeys } from '../lib/queryClient';
import type { ApiResource, Wallet } from '../types/api';

async function fetchWallet(): Promise<Wallet> {
  const { data } = await api.get<ApiResource<Wallet>>('/api/wallet');

  return data.data;
}

export function useWallet() {
  return useQuery({
    queryKey: queryKeys.wallet,
    queryFn: fetchWallet,
    // Polling: without a WebSocket/broadcast backend, this is the only way
    // a recipient's balance reflects money someone else just sent them.
    refetchInterval: 15_000,
  });
}

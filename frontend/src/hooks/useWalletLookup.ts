import { useMutation } from '@tanstack/react-query';
import { api } from '../lib/api';
import type { ApiResource } from '../types/api';

export interface RecipientLookupResult {
  wallet_id: string;
  name: string;
}

async function lookupRecipient(identifier: string): Promise<RecipientLookupResult> {
  const params = identifier.includes('@') ? { email: identifier } : { cpf: identifier.replace(/\D/g, '') };

  const { data } = await api.get<ApiResource<RecipientLookupResult>>('/api/wallets/lookup', { params });

  return data.data;
}

export function useWalletLookup() {
  return useMutation({ mutationFn: lookupRecipient });
}

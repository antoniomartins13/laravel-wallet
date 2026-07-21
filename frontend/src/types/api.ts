export type TransactionType = 'deposit' | 'transfer_in' | 'transfer_out' | 'reversal';

export type TransactionStatus = 'pending' | 'completed' | 'reversed' | 'failed';

export interface User {
  id: string;
  name: string;
  email: string;
  cpf: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface Wallet {
  id: string;
  balance: number;
  created_at: string;
  updated_at: string;
}

export interface Transaction {
  id: string;
  wallet_id: string;
  related_wallet_id: string | null;
  related_transaction_id: string | null;
  type: TransactionType;
  status: TransactionStatus;
  amount: number;
  reference_id: string | null;
  is_reversed: boolean;
  created_at: string;
}

export interface PaginationLinks {
  first: string | null;
  last: string | null;
  prev: string | null;
  next: string | null;
}

export interface PaginationMeta {
  current_page: number;
  from: number | null;
  last_page: number;
  per_page: number;
  to: number | null;
  total: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: PaginationLinks;
  meta: PaginationMeta;
}

export interface ApiResource<T> {
  data: T;
}

/** Standardized domain error shape from the backend's global exception handler. */
export interface ApiErrorPayload {
  message: string;
  code?: string;
  errors?: Record<string, string[]>;
}

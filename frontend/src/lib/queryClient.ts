import { QueryClient } from '@tanstack/react-query';

export const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: 1,
      staleTime: 30_000,
      refetchOnWindowFocus: false,
    },
  },
});

export const queryKeys = {
  user: ['user'] as const,
  wallet: ['wallet'] as const,
  statement: (page: number, perPage: number) => ['statement', page, perPage] as const,
};

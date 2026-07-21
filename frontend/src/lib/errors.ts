import { isAxiosError } from 'axios';
import type { ApiErrorPayload } from '../types/api';

/**
 * Extracts a user-facing message from a failed API call: the backend's
 * standardized `{ message, code }` domain error, a validation `errors`
 * bag, or a generic fallback for anything unexpected (never a raw stack
 * trace — the backend already guarantees that).
 */
export function getApiErrorMessage(error: unknown, fallback = 'Algo deu errado. Tente novamente.'): string {
  if (isAxiosError<ApiErrorPayload>(error)) {
    const payload = error.response?.data;

    if (payload?.errors) {
      const firstField = Object.values(payload.errors)[0];
      if (firstField?.[0]) {
        return firstField[0];
      }
    }

    if (payload?.message) {
      return payload.message;
    }
  }

  return fallback;
}

export function getApiErrorCode(error: unknown): string | undefined {
  if (isAxiosError<ApiErrorPayload>(error)) {
    return error.response?.data?.code;
  }

  return undefined;
}

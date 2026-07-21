import { createContext } from 'react';

export type ToastVariant = 'success' | 'error';

export interface Toast {
  id: string;
  variant: ToastVariant;
  message: string;
}

export interface ToastContextValue {
  toasts: Toast[];
  showToast: (variant: ToastVariant, message: string) => void;
  dismissToast: (id: string) => void;
}

export const ToastContext = createContext<ToastContextValue | undefined>(undefined);

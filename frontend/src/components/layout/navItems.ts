import { ArrowLeftRight, Home, PlusCircle, Receipt, type LucideIcon } from 'lucide-react';

export interface NavItem {
  to: string;
  label: string;
  icon: LucideIcon;
  end: boolean;
}

export const navItems: NavItem[] = [
  { to: '/', label: 'Início', icon: Home, end: true },
  { to: '/deposit', label: 'Depositar', icon: PlusCircle, end: false },
  { to: '/transfer', label: 'Transferir', icon: ArrowLeftRight, end: false },
  { to: '/statement', label: 'Extrato', icon: Receipt, end: false },
];

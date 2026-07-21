import { Outlet } from 'react-router-dom';
import { Sidebar } from './Sidebar';
import { MobileHeader } from './MobileHeader';
import { MobileNav } from './MobileNav';
import { ToastViewport } from '../ui/ToastViewport';

export function AppLayout() {
  return (
    <div className="min-h-screen bg-surface md:flex">
      <Sidebar />
      <MobileHeader />

      <main className="flex-1 p-4 pb-20 md:p-8 md:pb-8">
        <Outlet />
      </main>

      <MobileNav />
      <ToastViewport />
    </div>
  );
}

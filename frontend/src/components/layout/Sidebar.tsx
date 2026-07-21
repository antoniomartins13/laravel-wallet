import { NavLink } from 'react-router-dom';
import { Logo } from '../Logo';
import { navItems } from './navItems';
import { UserFooter } from './UserFooter';

/** Desktop-only navy sidebar (md+); mobile uses MobileHeader + MobileNav instead. */
export function Sidebar() {
  return (
    <aside className="hidden w-64 flex-col bg-primary px-4 py-6 text-white md:flex">
      <Logo variant="horizontal-white" height={36} className="mb-8 px-2" />

      <nav className="flex flex-1 flex-col gap-1">
        {navItems.map(({ to, label, icon: Icon, end }) => (
          <NavLink
            key={to}
            to={to}
            end={end}
            className={({ isActive }) =>
              `flex items-center gap-3 rounded-lg border-l-2 px-3 py-2.5 text-sm font-medium transition-colors duration-150 ${
                isActive
                  ? 'border-gold bg-white/10 text-white'
                  : 'border-transparent text-white/70 hover:bg-white/5 hover:text-white'
              }`
            }
          >
            <Icon className="h-5 w-5" aria-hidden="true" />
            {label}
          </NavLink>
        ))}
      </nav>

      <UserFooter />
    </aside>
  );
}

import { NavLink } from 'react-router-dom';
import { navItems } from './navItems';

/** Fixed bottom tab bar shown below the `md` breakpoint, replacing the sidebar. */
export function MobileNav() {
  return (
    <nav className="fixed inset-x-0 bottom-0 z-40 flex items-center justify-around border-t border-black/5 bg-white py-1.5 md:hidden">
      {navItems.map(({ to, label, icon: Icon, end }) => (
        <NavLink
          key={to}
          to={to}
          end={end}
          className={({ isActive }) =>
            `flex min-w-[64px] flex-col items-center gap-0.5 rounded-lg px-2 py-1.5 text-[11px] font-medium transition-colors duration-150 ${
              isActive ? 'text-primary' : 'text-ink/50'
            }`
          }
        >
          <Icon className="h-5 w-5" aria-hidden="true" />
          {label}
        </NavLink>
      ))}
    </nav>
  );
}

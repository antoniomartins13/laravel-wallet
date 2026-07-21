import { useCallback, useEffect, useState, type ReactNode } from 'react';
import { api, ensureCsrfCookie } from '../lib/api';
import type { User } from '../types/api';
import { AuthContext, type LoginInput, type RegisterInput } from './AuthContext';

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  const fetchUser = useCallback(async () => {
    try {
      const { data } = await api.get<User>('/api/user');
      setUser(data);
    } catch {
      setUser(null);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchUser();
  }, [fetchUser]);

  const login = useCallback(
    async (input: LoginInput) => {
      await ensureCsrfCookie();
      await api.post('/login', input);
      await fetchUser();
    },
    [fetchUser],
  );

  const register = useCallback(
    async (input: RegisterInput) => {
      await ensureCsrfCookie();
      await api.post('/register', input);
      await fetchUser();
    },
    [fetchUser],
  );

  const logout = useCallback(async () => {
    await api.post('/logout');
    setUser(null);
  }, []);

  return (
    <AuthContext.Provider value={{ user, isLoading, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

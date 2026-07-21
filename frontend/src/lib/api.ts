import axios from 'axios';

export const API_URL = import.meta.env.VITE_API_URL ?? 'http://localhost:8080';

/**
 * Sanctum SPA authentication: cookies (httpOnly session + XSRF-TOKEN), never
 * a bearer token in localStorage. `withXSRFToken` makes axios attach the
 * X-XSRF-TOKEN header even though the API runs on a different port than the
 * SPA (cross-origin, but same registered "stateful" domain on the backend).
 */
export const api = axios.create({
  baseURL: API_URL,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    Accept: 'application/json',
  },
});

/**
 * Must be called once before the first state-changing request of a session
 * (login/register) so the backend issues the XSRF-TOKEN cookie.
 */
export async function ensureCsrfCookie(): Promise<void> {
  await api.get('/sanctum/csrf-cookie');
}

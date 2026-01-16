import axios from "axios";
import i18next from "i18next";
import { jwtDecode } from "jwt-decode";

type MyTokenPayload = {
  username: string;
  roles: string[];
  iat: number;
  exp: number;
};

let accessToken: string | null = null;
let refreshPromise: Promise<boolean> | null = null;

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true,
});

api.interceptors.request.use(async (config) => {
  const isRefreshRequest = config.url?.includes("/api/auth/token/refresh");

  if (refreshPromise && !isRefreshRequest) {
    await refreshPromise;
  }

  if (!isRefreshRequest) {
    const token = getAccessToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
  }

  const locale = i18next.language;
  if (config.url && !config.url.includes("locale=")) {
    const url = new URL(config.url, config.baseURL);
    url.searchParams.set("locale", locale);
    config.url = url.pathname + url.search;
  }

  return config;
});

export const setAccessToken = (token: string | null): void => {
  accessToken = token;
  if (token) {
    api.defaults.headers.common["Authorization"] = `Bearer ${token}`;
  } else {
    delete api.defaults.headers.common["Authorization"];
  }
};

export const getAccessToken = (): string | null => accessToken;

export const getPermissions = (): string[] => {
  const token = getAccessToken();
  if (!token) return [];
  try {
    const decoded = jwtDecode<MyTokenPayload>(token);
    return decoded.roles || [];
  } catch {
    return [];
  }
};

export async function isAuth(): Promise<boolean> {
  const token = getAccessToken();
  if (token) return true;

  if (refreshPromise) return refreshPromise;

  refreshPromise = (async () => {
    try {
      const res = await api.post("/api/auth/token/refresh", { client_type: "web" });
      setAccessToken(res.data.token);
      return true;
    } catch (e) {
      setAccessToken(null);
      return false;
    } finally {
      refreshPromise = null;
    }
  })();

  return refreshPromise;
}

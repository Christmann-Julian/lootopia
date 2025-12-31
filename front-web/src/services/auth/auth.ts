import axios from "axios";
import i18next from "i18next";
import { jwtDecode } from "jwt-decode";

type MyTokenPayload = {
  username: string;
  roles: string[];
  iat: number;
  exp: number;
};

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true,
});

api.interceptors.request.use((config) => {
  const locale = i18next.language;
  if (config.url) {
    const url = new URL(config.url, config.baseURL);
    url.searchParams.set("locale", locale);
    config.url = url.pathname + url.search;
  }
  return config;
});

let accessToken: string | null = null;

export const setAccessToken = (token: string | null) => {
  accessToken = token;
  if (token) {
    api.defaults.headers.common["Authorization"] = `Bearer ${token}`;
  } else {
    delete api.defaults.headers.common["Authorization"];
  }
};

export const getAccessToken = () => accessToken;

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

  try {
    const res = await api.post("/api/auth/token/refresh", { client_type: "web" });
    setAccessToken(res.data.token);
    return true;
  } catch (e) {
    return false;
  }
}

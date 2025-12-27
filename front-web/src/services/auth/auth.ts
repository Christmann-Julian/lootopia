import axios from "axios";
import { getLanguage } from "../../utils/i18nUtils";

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  withCredentials: true,
});

api.interceptors.request.use((config) => {
  const locale = getLanguage();
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

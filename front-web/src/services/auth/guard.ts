import { redirect } from "react-router";
import { api, setAccessToken, getAccessToken } from "./auth";
import { getLanguage } from "../../utils/i18nUtils";

export async function requireAuth() {
  const authenticated = await isAuth();

  if (authenticated) {
    return null;
  } else {
    throw redirect(`/${getLanguage()}`);
  }
}

export async function requireGuest() {
  const authenticated = await isAuth();

  if (!authenticated) {
    return null;
  } else {
    throw redirect(`/${getLanguage()}/dashboard`);
  }
}

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

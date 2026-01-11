import { redirect } from "react-router";
import { getPermissions, isAuth } from "./auth";
import i18n from "i18next";

export async function requireGuest() {
  const authenticated = await isAuth();

  if (!authenticated) {
    return null;
  } else {
    throw redirect(`/${i18n.language}/dashboard`);
  }
}

export async function requirePermission(permission: string) {
  const authenticated = await isAuth();

  if (!authenticated) {
    throw redirect(`/${i18n.language}`);
  }

  const userPermissions = getPermissions();
  if (userPermissions.includes(permission)) {
    return null;
  }

  throw redirect(`/${i18n.language}/dashboard?error=unauthorized`);
}

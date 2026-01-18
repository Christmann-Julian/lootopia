import { Outlet, redirect } from "react-router";
import { getPermissions, isAuth } from "../../services/auth";
import i18n from "i18next";

export async function clientLoader(): Promise<null> | never {
  const authenticated = await isAuth();

  if (!authenticated) {
    throw redirect(`/${i18n.language}`);
  }

  const userPermissions = getPermissions();
  if (userPermissions.includes("ROLE_ADMIN")) {
    return null;
  }

  throw redirect(`/${i18n.language}/dashboard?error=unauthorized`);
}

export default function AdminLayout() {
  return <Outlet />;
}

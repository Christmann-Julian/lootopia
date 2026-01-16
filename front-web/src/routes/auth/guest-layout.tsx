import { Outlet, redirect } from "react-router";
import { isAuth } from "../../services/auth";
import i18n from "i18next";

export async function clientLoader(): Promise<null> | never {
  const authenticated = await isAuth();

  if (!authenticated) {
    return null;
  } else {
    throw redirect(`/${i18n.language}/dashboard`);
  }
}

export default function GuestLayout() {
  return <Outlet />;
}

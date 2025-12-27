import { Outlet } from "react-router";
import { requireGuest } from "../../services/auth/guard";

export async function clientLoader() {
  return await requireGuest();
}

export default function GuestLayout() {
  return <Outlet />;
}

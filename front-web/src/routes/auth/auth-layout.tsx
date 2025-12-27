import { Outlet } from "react-router";
import { requireAuth } from "../../services/auth/guard";

export async function clientLoader() {
  return await requireAuth();
}

export default function AuthLayout() {
  return <Outlet />;
}

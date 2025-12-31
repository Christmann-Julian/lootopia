import { Outlet } from "react-router";
import { requirePermission } from "../../services/auth/guard";

export async function clientLoader() {
  return await requirePermission("ROLE_USER");
}

export default function AuthLayout() {
  return <Outlet />;
}

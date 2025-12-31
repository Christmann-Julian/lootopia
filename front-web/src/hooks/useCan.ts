import { getPermissions } from "../services/auth/auth";

export function useCan() {
  const permissions = getPermissions();

  return (permission: string) => permissions.includes(permission);
}

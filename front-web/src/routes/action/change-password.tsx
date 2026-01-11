import type { ApiErrorResponse } from "../../types/ApiType";
import { api } from "../../services/auth/auth";
import { type ClientActionFunctionArgs } from "react-router";

export async function clientAction({ request }: ClientActionFunctionArgs) {
  const data = await request.json();
  if (!data.id) {
    return { error: true };
  }

  try {
    await api.put(`/api/users/${data.id}/password`, data);
    return { success: true };
  } catch (err: any) {
    const apiError = err.response?.data as ApiErrorResponse;
    if (apiError?.details) {
      const firstError = Object.values(apiError.details)[0];
      return { error: firstError?.[0] || true };
    }
    return { error: true };
  }
}

export async function loader() {
  return new Response("Method Not Allowed", { status: 405 });
}

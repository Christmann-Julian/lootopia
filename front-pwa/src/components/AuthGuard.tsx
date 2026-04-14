import { useEffect, useState, type JSX } from "react";
import { Navigate } from "react-router-dom";
import { isAuth } from "../services/auth";

export default function AuthGuard({ children }: { children: JSX.Element }) {
  const [authenticated, setAuthenticated] = useState<boolean | null>(null);

  useEffect(() => {
    async function checkAuth() {
      const auth = await isAuth();
      setAuthenticated(auth);
    }
    checkAuth();
  }, []);

  if (authenticated === null) {
    return <div>Loading...</div>;
  }

  if (!authenticated) {
    return <Navigate to="/" replace />;
  }

  return children;
}

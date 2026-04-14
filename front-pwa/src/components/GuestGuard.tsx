import { useEffect, useState, type JSX } from "react";
import { Navigate } from "react-router-dom";
import { isAuth } from "../services/auth";

export default function GuestGuard({ children }: { children: JSX.Element }) {
  const [authenticated, setAuthenticated] = useState<boolean | null>(null);

  useEffect(() => {
    async function checkAuth() {
      const auth = await isAuth();
      setAuthenticated(auth);
    }
    checkAuth();
  }, []);

  if (authenticated === null) {
    return <div>Loading...</div>; // Affiche un écran de chargement pendant la vérification
  }

  if (authenticated) {
    return <Navigate to="/home" replace />; // Redirige vers le tableau de bord si déjà authentifié
  }

  return children;
}

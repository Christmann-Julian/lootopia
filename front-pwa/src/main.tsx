import { StrictMode, Suspense } from "react";
import { createRoot } from "react-dom/client";
import "./assets/css/index.css";
import App from "./App.tsx";
import "./services/i18n";

createRoot(document.getElementById("root")!).render(
  <StrictMode>
    <Suspense
      fallback={
        <div style={{ color: "white", padding: "2rem", textAlign: "center" }}>
          Chargement de l'interface...
        </div>
      }
    >
      <App />
    </Suspense>
  </StrictMode>
);

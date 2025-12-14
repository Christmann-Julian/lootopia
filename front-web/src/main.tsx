import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import "./services/i18n";
import "./assets/css/style.css";
import App from "./App.tsx";

createRoot(document.getElementById("root")!).render(
  <StrictMode>
    <App />
  </StrictMode>
);

import { useTranslation } from "react-i18next";
import "../../assets/css/login.css";

export function meta() {
  return [
    { title: "Connexion | Lootopia" },
    {
      name: "description",
      content: "Lootopia - Connectez-vous à votre compte pour accéder à votre tableau de bord et gérer vos activités.",
    },
  ];
}

export default function Login() {
  const { t } = useTranslation(["auth", "common"]);
  return (
    <div className="login-container">
      <div className="logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        <span className="logo-text">{t("appName", { ns: "common" })}</span>
      </div>

      <div className="card">
        <div className="card-header">
          <h1 className="card-title">{t("login.title")}</h1>
          <p className="card-description">{t("login.subtitle")}</p>
        </div>

        <div className="alert alert-error" id="errorAlert">
          Identifiants incorrects. Veuillez réessayer.
        </div>

        <form id="loginForm">
          <div className="form-group">
            <label className="label" htmlFor="email">
              {t("login.email")}
            </label>
            <div className="input-wrapper">
              <svg
                className="input-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
              <input
                type="email"
                id="email"
                className="input input-with-icon"
                placeholder={t("login.emailPlaceholder")}
                required
              />
            </div>
          </div>

          <div className="form-group">
            <label className="label" htmlFor="password">
              {t("login.password")}
            </label>
            <div className="input-wrapper">
              <svg
                className="input-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
              <input
                type="password"
                id="password"
                className="input input-with-icon"
                placeholder="••••••••"
                required
              />
              <svg
                className="password-toggle"
                id="togglePassword"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </div>
          </div>

          <div
            style={{
              display: "flex",
              justifyContent: "space-between",
              alignItems: "center",
              marginBottom: "24px",
            }}
          >
            <div className="checkbox-wrapper">
              <input type="checkbox" id="remember" className="checkbox" />
              <label htmlFor="remember" className="checkbox-label">
                {t("login.rememberMe")}
              </label>
            </div>
            <a href="#" className="link">
              {t("login.forgotPassword")}
            </a>
          </div>

          <button type="submit" className="button button-primary">
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
            >
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
              <polyline points="10 17 15 12 10 7"></polyline>
              <line x1="15" y1="12" x2="3" y2="12"></line>
            </svg>
            {t("login.loginButton")}
          </button>
        </form>

        <div className="card-footer">
          <p className="footer-text">
            {t("login.noAccount")} &nbsp;
            <a href="#" className="link">
              {t("login.createAccount")}
            </a>
          </p>
        </div>
      </div>
    </div>
  );
}

import "../../assets/css/success.css";
import { Link } from "react-router";
import { getLanguage } from "../../utils/i18nUtils";
import { useTranslation, Trans } from "react-i18next";

export function meta() {
  const { t } = useTranslation("auth");
  return [{ title: t("registerSuccess.metaTitle", { ns: "auth" }) }];
}

export default function RegisterSuccess() {
  const { t } = useTranslation("auth");
  return (
    <div className="success-container">
      <div className="card">
        <div className="success-icon">
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="3"
            strokeLinecap="round"
            strokeLinejoin="round"
          >
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
        </div>

        <h1 className="card-title">{t("registerSuccess.title")}</h1>
        <p className="card-description">{t("registerSuccess.message")}</p>

        <div className="info-box">
          <div className="info-item">
            <svg
              className="info-item-icon"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
            >
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <div className="info-item-content">
              <div className="info-item-title">{t("registerSuccess.infoTitle1")}</div>
              <div className="info-item-description">{t("registerSuccess.infoMessage1")}</div>
            </div>
          </div>

          <div className="info-item">
            <svg
              className="info-item-icon"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
            >
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            <div className="info-item-content">
              <div className="info-item-title">{t("registerSuccess.infoTitle2")}</div>
              <div className="info-item-description">{t("registerSuccess.infoMessage2")}</div>
            </div>
          </div>
        </div>

        <Link to={`/${getLanguage()}`} className="button button-primary">
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
          >
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          {t("registerSuccess.loginButton")}
        </Link>

        <p className="help-text">
          <Trans
            i18nKey="registerSuccess.helpText"
            ns="auth"
            components={{
              1: <Link to="#">contactez le support</Link>,
            }}
          />
        </p>
      </div>
    </div>
  );
}

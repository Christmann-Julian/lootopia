import { Link, type LinksFunction } from "react-router";
import { useTranslation } from "react-i18next";

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/not-found.css" },
];

export default function NotFound() {
  const { t } = useTranslation("common");
  return (
    <div className="error-container">
      <div className="error-number">404</div>

      <h1 className="error-title">{t("error404.title")}</h1>
      <p className="error-description">{t("error404.description")}</p>

      <div>
        <Link to="/" className="button button-primary">
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
          >
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          </svg>
          {t("error404.goHome")}
        </Link>
      </div>
    </div>
  );
}

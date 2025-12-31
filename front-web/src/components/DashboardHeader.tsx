import React, { useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import { useNavigate, Link, useLocation, useParams } from "react-router";
import { api, setAccessToken } from "../services/auth/auth";
import { locales, type Locale } from "../types/LocaleType";
import Cookies from "js-cookie";
import Toast from "./Toast";

type DashboardHeaderProps = {
  title: string;
};

const DashboardHeader: React.FC<DashboardHeaderProps> = ({ title }) => {
  const { lang } = useParams();
  const { t } = useTranslation(["auth", "common"]);
  const navigate = useNavigate();
  const location = useLocation();
  const [openDropdown, setOpenDropdown] = useState<string | null>(null);
  const [toast, setToast] = useState<{
    message: string;
    type: "success" | "error" | "info" | "warning";
  } | null>(null);
  const [isLoggingOut, setIsLoggingOut] = useState<boolean>(false);

  const toggleDropdown = (dropdown: string): void => {
    setOpenDropdown((prev) => (prev === dropdown ? null : dropdown));
  };

  const handleClickOutside = (event: MouseEvent): void => {
    const target = event.target as HTMLElement;
    if (!target.closest(".dropdown")) {
      setOpenDropdown(null);
    }
  };

  const getNewPath = (targetLang: string): string => {
    const segments = location.pathname.split("/");
    segments[1] = targetLang;
    return segments.join("/") + location.search;
  };

  const getLocaleByCode = (code: string): Locale | null =>
    locales.find((locale) => locale.code === code) || null;

  const handleLogout = async (): Promise<void> => {
    setIsLoggingOut(true);
    try {
      await api.post("/api/auth/logout");
    } catch (error) {
      setToast({ message: t("internalServerError", { ns: "common" }), type: "error" });
    } finally {
      setAccessToken(null);
      Cookies.remove("REFRESH_TOKEN", { path: "/" });
      navigate(`/${lang}`);
      setIsLoggingOut(false);
    }
  };

  useEffect(() => {
    if (openDropdown) {
      document.addEventListener("click", handleClickOutside);
    } else {
      document.removeEventListener("click", handleClickOutside);
    }

    return () => {
      document.removeEventListener("click", handleClickOutside);
    };
  }, [openDropdown]);

  return (
    <header className="header">
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}
      <h1>{title}</h1>
      <div className="header-right">
        <div className="dropdown">
          <button
            className="button button-outline button-icon dropdown-button"
            onClick={() => toggleDropdown("first")}
          >
            <img
              src={`https://flagcdn.com/w40/${getLocaleByCode(lang!)?.country_code.toLowerCase()}.webp`}
              width="30"
            />
          </button>
          <ul
            className={`dropdown-menu dropdown-menu-right ${
              openDropdown === "first" ? "active" : ""
            }`}
          >
            {locales.map((locale) => (
              <li key={locale.code} className="li-icon">
                <Link to={getNewPath(locale.code)} onClick={() => setOpenDropdown(null)}>
                  <img
                    src={`https://flagcdn.com/w20/${locale.country_code.toLowerCase()}.webp`}
                    width="20"
                  />
                  {locale.name}
                </Link>
              </li>
            ))}
          </ul>
        </div>

        <div className="dropdown">
          <button
            className="button button-outline button-icon dropdown-button"
            onClick={() => toggleDropdown("second")}
          >
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
            >
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </button>
          <ul
            className={`dropdown-menu dropdown-menu-right ${
              openDropdown === "second" ? "active" : ""
            }`}
          >
            <li className="li-icon">
              <svg
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                style={{ marginRight: "8px" }}
              >
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
              </svg>
              <button
                style={{ border: "none", background: "none", cursor: "pointer" }}
                onClick={handleLogout}
                disabled={isLoggingOut}
              >
                {isLoggingOut ? (
                  <svg
                    className="spinner-btn"
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    strokeWidth="2"
                  >
                    <circle cx="12" cy="12" r="10" strokeOpacity="0.25" />
                    <path d="M12 2a10 10 0 0 1 10 10" />
                  </svg>
                ) : (
                  t("logout.logoutButton")
                )}
              </button>
            </li>
          </ul>
        </div>
      </div>
    </header>
  );
};

export default DashboardHeader;

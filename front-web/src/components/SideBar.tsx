import { useTranslation } from "react-i18next";
import { NavLink, useParams } from "react-router";
import { useCan } from "../hooks/useCan";

export default function SideBar() {
  const { lang } = useParams();
  const { t } = useTranslation(["navigation", "common"]);
  const can = useCan();

  return (
    <aside className="sidebar">
      <div className="logo">
        <img
          src="/assets/images/logo_circle_256x256.png"
          width={48}
          height={48}
          alt="Lootopia Logo"
        />
        <span>{t("appName", { ns: "common" })}</span>
      </div>

      <div className="nav-section">
        <div className="nav-section-title">{t("menu")}</div>
        <NavLink
          className={({ isActive }) => `nav-item ${isActive ? "active" : ""}`}
          to={`/${lang}/dashboard`}
          end
        >
          <span className="icon">
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <rect x="3" y="3" width="7" height="7"></rect>
              <rect x="14" y="3" width="7" height="7"></rect>
              <rect x="14" y="14" width="7" height="7"></rect>
              <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
          </span>
          <span>{t("dashboard")}</span>
        </NavLink>
        <NavLink
          className={({ isActive }) => `nav-item ${isActive ? "active" : ""}`}
          to={`/${lang}/dashboard/hunts`}
        >
          <span className="icon">
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
          </span>
          <span>{t("hunts")}</span>
        </NavLink>
        <NavLink
          className={({ isActive }) => `nav-item ${isActive ? "active" : ""}`}
          to={`/${lang}/dashboard/rewards`}
        >
          <span className="icon">
            {/* Icône Cadeau (Rewards) */}
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <polyline points="20 12 20 22 4 22 4 12"></polyline>
              <rect x="2" y="7" width="20" height="5"></rect>
              <line x1="12" y1="22" x2="12" y2="7"></line>
              <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
              <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
            </svg>
          </span>
          <span>{t("rewards")}</span>
        </NavLink>
      </div>

      <div className="nav-section">
        <div className="nav-section-title">{t("settings")}</div>
        <NavLink
          className={({ isActive }) => `nav-item ${isActive ? "active" : ""}`}
          to={`/${lang}/dashboard/settings`}
        >
          <span className="icon">
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
          </span>
          <span>{t("settings")}</span>
        </NavLink>
      </div>

      {can("ROLE_ADMIN") && (
        <div className="nav-section">
          <div className="nav-section-title">{t("admin")}</div>
          <NavLink
            className={({ isActive }) => `nav-item ${isActive ? "active" : ""}`}
            to={`/${lang}/dashboard/admin/users`}
          >
            <span className="icon">
              <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              >
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </span>
            <span>{t("users")}</span>
          </NavLink>
          <NavLink
            className={({ isActive }) => `nav-item ${isActive ? "active" : ""}`}
            to={`/${lang}/dashboard/admin/badges`}
          >
            <span className="icon">
              <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              >
                <circle cx="12" cy="8" r="7"></circle>
                <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
              </svg>
            </span>
            <span>{t("badges")}</span>
          </NavLink>
          <NavLink
            className={({ isActive }) => `nav-item ${isActive ? "active" : ""}`}
            to={`/${lang}/dashboard/admin/ranks`}
          >
            <span className="icon">
              <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              >
                <line x1="12" y1="20" x2="12" y2="10"></line>
                <line x1="18" y1="20" x2="18" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="16"></line>
              </svg>
            </span>
            <span>{t("ranks")}</span>
          </NavLink>
          <NavLink
            className={({ isActive }) => `nav-item ${isActive ? "active" : ""}`}
            to={`/${lang}/dashboard/admin/categories`}
          >
            <span className="icon">
              <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              >
                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                <polyline points="2 12 12 17 22 12"></polyline>
                <polyline points="2 17 12 22 22 17"></polyline>
              </svg>
            </span>
            <span>{t("categories")}</span>
          </NavLink>
          <NavLink
            className={({ isActive }) => `nav-item ${isActive ? "active" : ""}`}
            to={`/${lang}/dashboard/admin/rarities`}
          >
            <span className="icon">
              <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              >
                <path d="M6 3h12l4 6-10 13L2 9Z"></path>
                <path d="M11 3 8 9l4 13"></path>
                <path d="M13 3l3 6-4 13"></path>
              </svg>
            </span>
            <span>{t("rarities")}</span>
          </NavLink>
        </div>
      )}
    </aside>
  );
}

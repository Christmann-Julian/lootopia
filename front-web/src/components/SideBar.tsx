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
          to={`/${lang}/dashboard/treasure-hunts`}
        >
          <span className="icon">
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
            >
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            </svg>
          </span>
          <span>{t("treasureHunts")}</span>
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
              >
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </span>
            <span>{t("users")}</span>
          </NavLink>
        </div>
      )}
    </aside>
  );
}

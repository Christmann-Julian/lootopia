import "../assets/css/ui/dashboard-header.css"
import "../assets/css/ui/button.css"
import React, { useState, useEffect } from "react"
import { locales, changeLanguage, getCurrentLocale } from "../utils/i18nUtils";
import { useTranslation } from "react-i18next";

type DashboardHeaderProps = {
  title: string
}

const DashboardHeader: React.FC<DashboardHeaderProps> = ({ title }) => {
  const { t } = useTranslation(["auth"]);
  const [openDropdown, setOpenDropdown] = useState<string | null>(null)

  const toggleDropdown = (dropdown: string) => {
    setOpenDropdown((prev) => (prev === dropdown ? null : dropdown))
  }

  const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement
    if (!target.closest(".dropdown")) {
      setOpenDropdown(null)
    }
  }

  const handleLanguageChange = async (langCode: string) => {
    await changeLanguage(langCode)
    setOpenDropdown(null)
  }

  useEffect(() => {
    if (openDropdown) {
      document.addEventListener("click", handleClickOutside)
    } else {
      document.removeEventListener("click", handleClickOutside)
    }

    return () => {
      document.removeEventListener("click", handleClickOutside)
    }
  }, [openDropdown])

  return (
    <header className="header">
      <h1>{title}</h1>
      <div className="header-right">
        <div className="dropdown">
          <button
            className="button button-outline button-icon dropdown-button"
            onClick={() => toggleDropdown("first")}
          >
            <img
              src={`https://flagcdn.com/w40/${getCurrentLocale().country_code.toLowerCase()}.webp`}
              width="30"/>
          </button>
          <ul
            className={`dropdown-menu dropdown-menu-right ${
              openDropdown === "first" ? "active" : ""
            }`}
          >
            {locales.map((locale) => (
              <li key={locale.code} className="li-icon" onClick={() => handleLanguageChange(locale.code)}>
                <img
                  src={`https://flagcdn.com/w20/${locale.country_code.toLowerCase()}.webp`}
                  width="20"/>
                {locale.name}
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
              <span>{t("logout.logoutButton")}</span>
            </li>
          </ul>
        </div>
      </div>
    </header>
  )
}

export default DashboardHeader

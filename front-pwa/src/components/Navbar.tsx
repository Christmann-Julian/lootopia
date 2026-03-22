import { Map as MapIcon, Backpack, Settings, LayoutGrid } from "lucide-react";
import "../assets/css/ui/navbar.css";
import { NavLink } from "react-router-dom";
import { useTranslation } from "react-i18next";

type NavbarProps = {
  activeItem: string;
};

const Navbar: React.FC<NavbarProps> = ({ activeItem }) => {
  const { t } = useTranslation();

  return (
    <nav className="nav-bar">
      <NavLink to="/home" className={`nav-link ${activeItem === "home" ? "active" : ""}`}>
        <LayoutGrid size={24} />
        <span>{t("navbar.home")}</span>
      </NavLink>
      <NavLink
        to="/treasure-hunts"
        className={`nav-link ${activeItem === "radar" ? "active" : ""}`}
      >
        <MapIcon size={24} />
        <span>{t("navbar.radar")}</span>
      </NavLink>
      <NavLink
        to="/rewards"
        className={`nav-link ${activeItem === "rewards" ? "active" : ""}`}
        style={{ position: "relative" }}
      >
        {/* <div className="notification-dot">3</div> */}
        <Backpack size={24} />
        <span>{t("navbar.rewards")}</span>
      </NavLink>
      <NavLink to="/profile" className={`nav-link ${activeItem === "profile" ? "active" : ""}`}>
        <Settings size={24} />
        <span>{t("navbar.profile")}</span>
      </NavLink>
    </nav>
  );
};

export default Navbar;

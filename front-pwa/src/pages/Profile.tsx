import { useState } from "react";
import {
  User,
  Star,
  Ticket,
  Target,
  Compass,
  Flame,
  Lock,
  Trash2,
  Save,
  Hexagon,
  ShieldCheck,
  Mail,
  Globe,
} from "lucide-react";
import "../assets/css/profile.css";
import Navbar from "../components/Navbar";
import { useTranslation } from "react-i18next";

const Profile = ({ username = "Explorateur Alpha" }) => {
  const { t, i18n } = useTranslation();

  const [profileData, setProfileData] = useState({
    firstName: "Explorateur",
    lastName: "Alpha",
    pseudo: "alpha_123",
    email: "alpha@lootopia.net",
  });

  const stats = [
    { label: t("profile.stats.hunts"), value: "42", icon: <Target size={14} /> },
    { label: t("profile.stats.rewards"), value: "15", icon: <Ticket size={14} /> },
  ];

  const badges = [
    { name: "Pionnier", icon: <Compass size={24} />, color: "#d4af37" },
    { name: "Traqueur", icon: <Target size={24} />, color: "#38bdf8" },
    { name: "Élite BK", icon: <Star size={24} />, color: "#fb7185" },
  ];

  const changeLanguage = (lng: string) => {
    i18n.changeLanguage(lng);
  };

  return (
    <div className="profile-wrapper">
      <div className="topo-overlay"></div>

      <div className="content-container">
        <header className="profile-header">
          <div className="avatar-ring">
            <div className="avatar-main">
              <User size={32} />
            </div>
            <div className="level-badge">14</div>
          </div>
          <h1 className="username">{username}</h1>
          <div className="rank-label">Explorateur de Grade S</div>

          <div className="xp-container">
            <div className="xp-info-row">
              <span>SYNC XP : 85%</span>
              <div className="streak-badge">
                <Flame size={10} fill="currentColor" />
                <span>5J SÉRIE</span>
              </div>
            </div>
            <div className="xp-bar">
              <div className="xp-fill"></div>
            </div>
          </div>
        </header>

        <div className="stats-grid">
          {stats.map((stat, i) => (
            <div key={i} className="stat-card">
              <span className="stat-value">{stat.value}</span>
              <span className="stat-label">{stat.label}</span>
            </div>
          ))}
        </div>

        <h3 className="section-title">{t("profile.badges.title")}</h3>
        <div className="badges-list">
          {badges.map((badge, i) => (
            <div key={i} className="badge-item">
              <div className="badge-icon-container">{badge.icon}</div>
              <span className="badge-name">{badge.name}</span>
            </div>
          ))}
          <div className="badge-item locked">
            <div className="badge-icon-container mystery">
              <Hexagon size={20} />
            </div>
            <span className="badge-name">???</span>
          </div>
        </div>

        <h3 className="section-title">{t("profile.preferences.title")}</h3>
        <div className="tactical-group">
          <div className="input-field">
            <label className="input-label">{t("profile.preferences.language")}</label>
            <div className="input-wrapper">
              <select
                className="tactical-input select-input"
                value={i18n.language.split("-")[0]}
                onChange={(e) => changeLanguage(e.target.value)}
              >
                <option value="fr">🇫🇷 Français</option>
                <option value="en">🇬🇧 English</option>
              </select>
              <Globe size={16} className="field-icon" />
            </div>
          </div>
        </div>

        <h3 className="section-title">{t("profile.info.title")}</h3>
        <div className="tactical-group">
          <div className="name-grid">
            <div className="input-field">
              <label className="input-label">{t("input.firstName")}</label>
              <div className="input-wrapper">
                <input
                  type="text"
                  className="tactical-input"
                  value={profileData.firstName}
                  onChange={(e) => setProfileData({ ...profileData, firstName: e.target.value })}
                />
                <User size={16} className="field-icon" />
              </div>
            </div>
            <div className="input-field">
              <label className="input-label">{t("input.lastName")}</label>
              <div className="input-wrapper">
                <input
                  type="text"
                  className="tactical-input"
                  value={profileData.lastName}
                  onChange={(e) => setProfileData({ ...profileData, lastName: e.target.value })}
                />
                <User size={16} className="field-icon" />
              </div>
            </div>
          </div>

          <div className="input-field">
            <label className="input-label">{t("input.pseudo")}</label>
            <div className="input-wrapper">
              <input
                type="text"
                className="tactical-input"
                value={profileData.pseudo}
                onChange={(e) => setProfileData({ ...profileData, pseudo: e.target.value })}
              />
              <Mail size={16} className="field-icon" />
            </div>
          </div>

          <div className="input-field">
            <label className="input-label">{t("input.email")}</label>
            <div className="input-wrapper">
              <input
                type="email"
                className="tactical-input"
                value={profileData.email}
                onChange={(e) => setProfileData({ ...profileData, email: e.target.value })}
              />
              <Mail size={16} className="field-icon" />
            </div>
          </div>

          <button className="btn btn-primary">
            <Save size={18} />
            <span>{t("profile.actions.edit")}</span>
          </button>
        </div>

        <h3 className="security-title">{t("profile.security.title")}</h3>
        <div className="tactical-group">
          <div className="input-field">
            <label className="input-label">{t("input.oldPassword")}</label>
            <div className="input-wrapper">
              <input type="password" placeholder="••••••••" className="tactical-input" />
              <Lock size={16} className="field-icon" />
            </div>
          </div>
          <div className="input-field">
            <label className="input-label">{t("input.newPassword")}</label>
            <div className="input-wrapper">
              <input type="password" placeholder="••••••••" className="tactical-input" />
              <Lock size={16} className="field-icon" />
            </div>
          </div>
          <button className="btn btn-primary">
            <ShieldCheck size={18} />
            <span>{t("profile.actions.edit")}</span>
          </button>
        </div>

        <h3 className="section-title danger-text">{t("profile.delete.title")}</h3>
        <div className="tactical-group danger-border">
          <p className="danger-notice">{t("profile.delete.notice")}</p>
          <button className="btn btn-danger">
            <Trash2 size={16} />
            <span>{t("profile.actions.delete")}</span>
          </button>
        </div>
      </div>

      <Navbar activeItem="profile" />
    </div>
  );
};

export default Profile;

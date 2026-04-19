import { useState, useEffect } from "react";
import { useForm } from "react-hook-form";
import { useTranslation } from "react-i18next";
import {
  User,
  Ticket,
  Target,
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
import Toast from "../components/Toast";
import { api } from "../services/auth";
import { getBadgeIcon } from "../services/badgeIconService";
import { useNavigate } from "react-router-dom";
import { type PersonalInfoForm, type SecurityForm } from "../types/FormType";
import { type UserProfileData, type TranslatedItem } from "../types/DataTypes";
import axios from "axios";

type ToastState = {
  show: boolean;
  message: string;
  type: "success" | "error" | "info" | "warning";
};

const Profile = () => {
  const { t, i18n } = useTranslation();
  const [userData, setUserData] = useState<UserProfileData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [toast, setToast] = useState<ToastState>({ show: false, message: "", type: "info" });
  const [userId, setCurrentUserId] = useState<number | null>(null);
  const navigate = useNavigate();

  const {
    register: registerInfo,
    handleSubmit: handleInfoSubmit,
    reset: resetInfo,
    formState: { errors: infoErrors, isSubmitting: isSubmittingInfo },
  } = useForm<PersonalInfoForm>();

  const {
    register: registerSecurity,
    handleSubmit: handleSecuritySubmit,
    reset: resetSecurity,
    formState: { errors: securityErrors, isSubmitting: isSubmittingSecurity },
  } = useForm<SecurityForm>();

  useEffect(() => {
    const fetchProfileData = async () => {
      try {
        setIsLoading(true);

        const meResponse = await api.get("/api/auth/me");
        const userId = meResponse.data.id;
        setCurrentUserId(userId);

        const profileResponse = await api.get(`/api/users/${userId}`);
        const data = profileResponse.data;

        setUserData(data);

        resetInfo({
          firstname: data.firstname,
          lastname: data.lastname,
          pseudo: data.pseudo,
          email: data.email,
        });
      } catch (error: unknown) {
        console.error("Erreur d'authentification ou de profil:", error);

        if (axios.isAxiosError(error) && error.response && error.response.status === 401) {
          navigate("/login");
        } else {
          showToast(t("error.serverError"), "error");
        }
      } finally {
        setIsLoading(false);
      }
    };

    fetchProfileData();
  }, [resetInfo, t, navigate]);

  const showToast = (message: string, type: ToastState["type"]) => {
    setToast({ show: true, message, type });
  };

  const changeLanguage = (lng: string) => {
    i18n.changeLanguage(lng);
  };

  const onSubmitInfo = async (data: PersonalInfoForm) => {
    try {
      const response = await api.put(`/api/users/${userId}`, data);
      setUserData(response.data);
      showToast(t("profile.actions.edit"), "success");
    } catch (error) {
      console.error(error);
      showToast(t("error.serverError"), "error");
    }
  };

  const onSubmitSecurity = async (data: SecurityForm) => {
    try {
      await api.put(`/api/users/${userId}/password`, data);
      showToast(t("profile.actions.passwordUpdated"), "success");
      resetSecurity();
    } catch (error) {
      console.error(error);
      showToast(t("error.invalidCredentials"), "error");
    }
  };

  const handleDeleteAccount = async () => {
    if (window.confirm(t("profile.delete.notice"))) {
      try {
        await api.delete(`/api/users/${userId}`);
        navigate("/login");
      } catch (error) {
        console.error(error);
        showToast(t("error.serverError"), "error");
      }
    }
  };

  if (isLoading || !userData) {
    return (
      <div className="profile-wrapper">
        <div className="content-container">Chargement...</div>
      </div>
    );
  }

  const currentLang = i18n.language?.split("-")[0] || "fr";

  const getTranslatedName = (item: TranslatedItem | undefined | null, fallback: string) => {
    if (!item) return fallback;
    return item.translations?.[currentLang] || item.name || fallback;
  };

  let xpPercentage = 0;

  if (userData.rank && userData.rank.experienceMax > userData.rank.experienceMin) {
    const { experienceMin, experienceMax } = userData.rank;
    const currentExperience = userData.experience || 0;
    const levelTotalXp = experienceMax - experienceMin;
    const xpGainedInLevel = currentExperience - experienceMin;

    xpPercentage = Math.max(0, Math.min(100, (xpGainedInLevel / levelTotalXp) * 100));
  }

  return (
    <div className="profile-wrapper">
      <div className="topo-overlay"></div>

      {toast.show && (
        <Toast
          message={toast.message}
          type={toast.type}
          onClose={() => setToast({ ...toast, show: false })}
        />
      )}

      <div className="content-container">
        <header className="profile-header">
          <div className="avatar-ring">
            <div className="avatar-main">
              <User size={32} />
            </div>
            <div className="level-badge">{userData.rank?.level || 1}</div>
          </div>
          <h1 className="username">{userData.pseudo}</h1>
          <div className="rank-label">{getTranslatedName(userData.rank, "Explorateur Novice")}</div>

          <div className="xp-container">
            <div className="xp-info-row">
              <span>SYNC XP : {Math.round(xpPercentage)}%</span>
              <div className="streak-badge">
                <Flame size={10} fill="currentColor" />
                <span>
                  EXP: {userData.experience} / {userData.rank?.experienceMax || 1}
                </span>
              </div>
            </div>
            <div className="xp-bar">
              <div className="xp-fill" style={{ width: `${xpPercentage}%` }}></div>
            </div>
          </div>
        </header>

        <div className="stats-grid">
          <div className="stat-card">
            <span className="stat-value">{userData.huntCount}</span>
            <span className="stat-label">{t("profile.stats.hunts")}</span>
            <Target size={14} className="stat-bg-icon" />
          </div>
          <div className="stat-card">
            <span className="stat-value">{userData.rewardCount}</span>
            <span className="stat-label">{t("profile.stats.rewards")}</span>
            <Ticket size={14} className="stat-bg-icon" />
          </div>
        </div>

        <h3 className="section-title">{t("profile.badges.title")}</h3>
        <div className="badges-list">
          {userData.badges.map((badge) => (
            <div key={badge.id} className="badge-item">
              <div className="badge-icon-container">{getBadgeIcon(badge.icon, 24)}</div>
              <span className="badge-name">{getTranslatedName(badge, "Badge")}</span>
            </div>
          ))}
          {userData.badges.length === 0 && (
            <div className="badge-item locked">
              <div className="badge-icon-container mystery">
                <Hexagon size={20} />
              </div>
              <span className="badge-name">???</span>
            </div>
          )}
        </div>

        <h3 className="section-title">{t("profile.preferences.title")}</h3>
        <div className="tactical-group">
          <div className="input-field">
            <label className="input-label">{t("profile.preferences.language")}</label>
            <div className="input-wrapper">
              <select
                className="tactical-input select-input"
                value={i18n.language?.split("-")[0] || "fr"}
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
        <form onSubmit={handleInfoSubmit(onSubmitInfo)} className="tactical-group">
          <div className="name-grid">
            <div className="input-field">
              <label className="input-label">{t("input.firstName")}</label>
              <div className="input-wrapper">
                <input
                  type="text"
                  className="tactical-input"
                  {...registerInfo("firstname", { required: t("error.requiredField") })}
                />
                <User size={16} className="field-icon" />
              </div>
              {infoErrors.firstname && (
                <span className="error-text">{infoErrors.firstname.message}</span>
              )}
            </div>
            <div className="input-field">
              <label className="input-label">{t("input.lastName")}</label>
              <div className="input-wrapper">
                <input
                  type="text"
                  className="tactical-input"
                  {...registerInfo("lastname", { required: t("error.requiredField") })}
                />
                <User size={16} className="field-icon" />
              </div>
              {infoErrors.lastname && (
                <span className="error-text">{infoErrors.lastname.message}</span>
              )}
            </div>
          </div>

          <div className="input-field">
            <label className="input-label">{t("input.pseudo")}</label>
            <div className="input-wrapper">
              <input
                type="text"
                className="tactical-input"
                {...registerInfo("pseudo", { required: t("error.requiredField") })}
              />
              <User size={16} className="field-icon" />
            </div>
            {infoErrors.pseudo && <span className="error-text">{infoErrors.pseudo.message}</span>}
          </div>

          <div className="input-field">
            <label className="input-label">{t("input.email")}</label>
            <div className="input-wrapper">
              <input
                type="email"
                className="tactical-input"
                {...registerInfo("email", {
                  required: t("error.requiredField"),
                  pattern: { value: /^\S+@\S+$/i, message: t("error.invalidEmail") },
                })}
              />
              <Mail size={16} className="field-icon" />
            </div>
            {infoErrors.email && <span className="error-text">{infoErrors.email.message}</span>}
          </div>

          <button type="submit" className="btn btn-primary" disabled={isSubmittingInfo}>
            <Save size={18} />
            <span>{isSubmittingInfo ? "..." : t("profile.actions.edit")}</span>
          </button>
        </form>

        <h3 className="security-title">{t("profile.security.title")}</h3>
        <form onSubmit={handleSecuritySubmit(onSubmitSecurity)} className="tactical-group">
          <div className="input-field">
            <label className="input-label">{t("input.oldPassword")}</label>
            <div className="input-wrapper">
              <input
                type="password"
                placeholder="••••••••"
                className="tactical-input"
                {...registerSecurity("currentPassword", { required: t("error.requiredField") })}
              />
              <Lock size={16} className="field-icon" />
            </div>
            {securityErrors.currentPassword && (
              <span className="error-text">{securityErrors.currentPassword.message}</span>
            )}
          </div>

          <div className="input-field">
            <label className="input-label">{t("input.newPassword")}</label>
            <div className="input-wrapper">
              <input
                type="password"
                placeholder="••••••••"
                className="tactical-input"
                {...registerSecurity("newPassword", {
                  required: t("error.requiredField"),
                  minLength: { value: 6, message: t("error.passwordTooShort") },
                })}
              />
              <Lock size={16} className="field-icon" />
            </div>
            {securityErrors.newPassword && (
              <span className="error-text">{securityErrors.newPassword.message}</span>
            )}
          </div>

          <button type="submit" className="btn btn-primary" disabled={isSubmittingSecurity}>
            <ShieldCheck size={18} />
            <span>{isSubmittingSecurity ? "..." : t("profile.actions.edit")}</span>
          </button>
        </form>

        <h3 className="section-title danger-text">{t("profile.delete.title")}</h3>
        <div className="tactical-group danger-border">
          <p className="danger-notice">{t("profile.delete.notice")}</p>
          <button type="button" onClick={handleDeleteAccount} className="btn btn-danger">
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

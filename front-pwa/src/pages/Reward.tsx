import { useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import {
  Ticket,
  Clock,
  ShoppingBag,
  ChevronRight,
  Zap,
  Tag,
  AlertCircle,
  User,
  Milestone,
} from "lucide-react";
import "../assets/css/reward.css";
import Navbar from "../components/Navbar";
import { api } from "../services/auth";
import type { RewardData, UserPseudoData } from "../types/DataTypes";

const Reward = () => {
  const { t } = useTranslation();

  const [rewards, setRewards] = useState<RewardData[]>([]);
  const [userInfo, setUserInfo] = useState<UserPseudoData>({ pseudo: "Agent" });
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    api
      .get("/api/auth/me")
      .then((res) => {
        if (res.data) {
          setUserInfo({ pseudo: res.data.pseudo || `${res.data.firstname} ${res.data.lastname}` });
        }
      })
      .catch((err) => console.error("Erreur auth/me:", err));

    api
      .get("/api/me/rewards")
      .then((res) => {
        if (res.data && res.data.data) {
          setRewards(res.data.data);
        }
      })
      .catch((err) => console.error("Erreur lors de la récupération des récompenses:", err))
      .finally(() => setIsLoading(false));
  }, []);

  const calculateExpiry = (endDateString: string) => {
    const end = new Date(endDateString).getTime();
    const now = new Date().getTime();
    const diffMs = end - now;

    if (diffMs <= 0) return t("reward.expired");

    const days = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diffMs % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));

    if (days > 0) {
      return `${days}${t("reward.days")} ${hours}${t("reward.hours")}`;
    }

    const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
    return `${hours}${t("reward.hours")} ${minutes}${t("reward.minutes")}`;
  };

  const handleRewardClick = (link: string) => {
    if (link) {
      window.open(link, "_blank", "noopener,noreferrer");
    }
  };

  return (
    <div className="rewards-wrapper">
      <div className="topo-overlay"></div>

      <div className="content-container">
        <header className="page-header">
          <div className="user-hud">
            <div className="avatar-box">
              <User size={20} />
            </div>
            <div>
              <div className="user-name">{userInfo.pseudo}</div>
              <div className="inventory-label">{t("reward.inventoryLabel")}</div>
            </div>
          </div>
          <div className="ticket-counter">
            <Ticket size={18} color="var(--gold)" />
            <span className="ticket-count">{rewards.length}</span>
          </div>
        </header>

        <div className="title-section">
          <h1 className="page-title">{t("reward.title")}</h1>
          <p className="page-subtitle">{t("reward.subtitle")}</p>
        </div>

        <div className="rewards-list">
          {isLoading ? (
            <div className="empty-state">
              <p>{t("reward.loading")}</p>
            </div>
          ) : rewards.length > 0 ? (
            rewards.map((item) => (
              <div key={item.id} className="reward-card">
                <div className="ticket-cut cut-top"></div>
                <div className="ticket-cut cut-bottom"></div>

                <div className="reward-main">
                  <div className="rarity-tag">
                    <Zap size={10} fill="currentColor" />
                    {item.rarity || t("reward.standardRarity")}
                  </div>
                  <div className="brand-label">{item.company || t("reward.mysteryPartner")}</div>
                  <h3 className="reward-title">{item.title || t("reward.defaultTitle")}</h3>

                  <div className="reward-metadata">
                    <div className="meta-item">
                      <Tag size={12} color="var(--gold)" />
                      <span>{item.category || t("reward.generalCategory")}</span>
                    </div>
                    <div className="meta-item">
                      <Milestone size={12} color="var(--gold)" />
                      <span>
                        {t("reward.codePrefix")}
                        {item.code}
                      </span>
                    </div>
                  </div>
                </div>

                <div className="reward-action">
                  <div className="expiry-timer">
                    <Clock size={16} />
                    <span>{calculateExpiry(item.endDate)}</span>
                  </div>
                  <button
                    className="extract-btn"
                    title={t("reward.viewBtn")}
                    onClick={() => handleRewardClick(item.link)}
                    disabled={new Date(item.endDate).getTime() <= new Date().getTime()}
                  >
                    <ChevronRight size={26} />
                  </button>
                </div>
              </div>
            ))
          ) : (
            <div className="empty-state">
              <ShoppingBag size={48} className="empty-icon" />
              <h3 className="empty-title">{t("reward.emptyTitle")}</h3>
              <p className="empty-text">{t("reward.emptyText")}</p>
            </div>
          )}
        </div>

        <div className="protocol-banner">
          <AlertCircle size={24} color="var(--gold)" className="protocol-icon" />
          <div>
            <span className="protocol-title">{t("reward.protocolTitle")}</span>
            <p className="protocol-text">{t("reward.protocolText")}</p>
          </div>
        </div>
      </div>

      <Navbar activeItem="rewards" />
    </div>
  );
};

export default Reward;

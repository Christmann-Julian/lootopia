import { useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import {
  Compass,
  Award,
  Zap,
  Flame,
  MapPin,
  LocateFixed,
  Navigation,
  Milestone,
  Lock,
} from "lucide-react";
import "../assets/css/home.css";
import Navbar from "../components/Navbar";
import { api } from "../services/auth";
import { getBadgeIcon } from "../services/badgeIconService";
import { Link } from "react-router-dom";
import type { UserHomeData, HuntSponsoredData } from "../types/DataTypes";

interface MenuProps {
  onStartRadar?: () => void;
}

const Home: React.FC<MenuProps> = () => {
  const { t, i18n } = useTranslation();

  const [userInfo, setUserInfo] = useState<UserHomeData>({
    pseudo: "Agent",
    experience: 0,
    level: 1,
    rankName: "PIONNIER",
  });
  const [sponsoredHunts, setSponsoredHunts] = useState<HuntSponsoredData[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    api
      .get("/api/auth/me")
      .then((res) => {
        if (res.data && res.data.id) {
          return api.get(`/api/users/${res.data.id}`);
        }
        throw new Error("ID not found in /me response");
      })
      .then((res) => {
        if (res && res.data) {
          setUserInfo({
            pseudo: res.data.pseudo || `${res.data.firstname} ${res.data.lastname}` || "Agent",
            experience: res.data.experience || 0,
            level: res.data.rankLevel || res.data.rank?.level || 1,
            rankName: res.data.rank?.translations?.[i18n.language] || "PIONNIER",
            rank: res.data.rank,
          });
        }
      })
      .catch((err) => console.error("User details error:", err));

    api
      .get("/api/hunts/sponsored?limit=10")
      .then((res) => {
        if (res.data?.data) {
          setSponsoredHunts(res.data.data);
        }
      })
      .catch((err) => console.error("Sponsored hunts error:", err))
      .finally(() => setIsLoading(false));
  }, [i18n.language]);

  let xpProgress = 0;
  if (userInfo.rank && userInfo.rank.experienceMax > userInfo.rank.experienceMin) {
    const { experienceMin, experienceMax } = userInfo.rank;
    const currentExperience = userInfo.experience || 0;

    const levelTotalXp = experienceMax - experienceMin;
    const xpGainedInLevel = currentExperience - experienceMin;

    xpProgress = Math.max(0, Math.min(100, (xpGainedInLevel / levelTotalXp) * 100));
  }

  const featuredHunt = sponsoredHunts.length > 0 ? sponsoredHunts[0] : null;
  const gridHunts = sponsoredHunts.slice(1);

  return (
    <div className="menu-wrapper">
      <div className="topo-overlay"></div>

      <div className="menu-view">
        <header className="header-section">
          <div className="avatar-box">
            <LocateFixed size={24} />
          </div>
          <div className="header-content">
            <div className="header-top">
              <span className="username">{userInfo.pseudo}</span>
              <div className="streak">
                <Flame size={16} fill="currentColor" />
                <span className="streak-text">{t("home.streak", { days: 5 })}</span>
              </div>
            </div>

            <div className="xp-bar">
              <div className="xp-fill" style={{ width: `${xpProgress}%` }}></div>
            </div>

            <div className="xp-info">
              <span>
                {t("home.rank", { rank: userInfo.rankName?.toUpperCase() || "PIONNIER" })} - NIVEAU{" "}
                {userInfo.level}
              </span>
              <span>
                {userInfo.experience.toLocaleString()} {t("home.xp")}
              </span>
            </div>
          </div>
        </header>

        {isLoading ? (
          <div
            className="loading-state"
            style={{ textAlign: "center", padding: "2rem", color: "var(--gold)" }}
          >
            <Zap size={32} className="spinning-icon" />
            <p style={{ marginTop: "1rem" }}>{t("home.loading")}</p>
          </div>
        ) : (
          <>
            <section className="tactical-card">
              <div className="scan-info">{t("home.scanInfo")}</div>

              {featuredHunt ? (
                <>
                  <div className="target-info">
                    <div>
                      <span className="brand-label">
                        <Milestone size={10} />
                        {t("home.targetDetected")} • {featuredHunt.company}
                      </span>
                      <h2 className="loot-title">
                        {t("home.lootBox")} : {featuredHunt.title}
                      </h2>
                    </div>
                  </div>

                  <div className="reward-info">
                    <div className="reward-icon">
                      {getBadgeIcon(featuredHunt.category?.icon, 40)}
                    </div>
                    <div className="reward-details">
                      <div className="reward-text">
                        {t("home.rewardPrefix")}{" "}
                        <span className="reward-highlight">
                          {featuredHunt.reward?.title || "???"}
                        </span>
                      </div>
                      <div className="reward-distance">
                        <Navigation size={12} fill="currentColor" />
                        <span>{t("home.distance") + featuredHunt.location}</span>
                      </div>
                    </div>
                  </div>
                </>
              ) : (
                <div style={{ padding: "2rem 0", textAlign: "center", color: "#888" }}>
                  <MapPin size={48} style={{ opacity: 0.5, margin: "0 auto 1rem" }} />
                  <p>{t("home.noSponsored")}</p>
                </div>
              )}

              <Link className="main-btn" to={`/radar/${featuredHunt ? featuredHunt.id : ""}`}>
                <Compass size={22} />
                <span>{t("home.initRadar")}</span>
              </Link>
            </section>

            {gridHunts.length > 0 && (
              <>
                <div className="sponsored-header">
                  <h3>{t("home.sponsoredTitle")}</h3>
                  <Link className="view-all" to="/treasure-hunts">
                    {t("home.viewAll")}
                  </Link>
                </div>

                <div className="sector-grid">
                  {gridHunts.map((hunt) => {
                    const reqXp = hunt.rarity?.minExperience || 0;
                    const isLocked = reqXp > userInfo.experience;

                    return (
                      <Link
                        key={hunt.id}
                        className={`sector-card ${isLocked ? "locked" : ""}`}
                        to={`/radar/${hunt.id}`}
                      >
                        <div className="brand-header">
                          <span className="brand-label">{hunt.company}</span>
                          <span
                            className="reward-pill"
                            style={{ background: isLocked ? "#4b5563" : "var(--gold)" }}
                          >
                            {isLocked ? t("home.locked") : hunt.rarity?.name || "???"}
                          </span>
                        </div>
                        <h4 className="sector-title">
                          {isLocked ? (
                            <Lock size={20} color="#4b5563" />
                          ) : (
                            getBadgeIcon(hunt.category?.icon, 20, "var(--gold)")
                          )}{" "}
                          {hunt.title}
                        </h4>
                        <p className="sector-desc">
                          {isLocked
                            ? t("home.levelRequired", { level: reqXp })
                            : hunt.description || t("home.noDescription")}
                        </p>

                        <div className={`sector-footer ${isLocked ? "locked-footer" : ""}`}>
                          {isLocked ? (
                            <>
                              <Award size={12} />
                              <span>{t("home.levelRequired", { level: reqXp })}</span>
                            </>
                          ) : (
                            <>
                              <MapPin size={12} />
                              <span>
                                {t("home.sectorPrefix")} {hunt.location || "XXX"}
                              </span>
                            </>
                          )}
                        </div>
                      </Link>
                    );
                  })}
                </div>
              </>
            )}
          </>
        )}
      </div>

      <Navbar activeItem="home" />
    </div>
  );
};

export default Home;

import { useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import { MapPin, ChevronLeft, ChevronRight, Search, Target } from "lucide-react";
import "../assets/css/treasure-hunt.css";
import Navbar from "../components/Navbar";
import { api } from "../services/auth";
import { getBadgeIcon } from "../services/badgeIconService";
import type { CategoryData, HuntData, UserStatsData } from "../types/DataTypes";
import { Link } from "react-router-dom";

const TreasureHunt = () => {
  const { t } = useTranslation();

  const [hunts, setHunts] = useState<HuntData[]>([]);
  const [categories, setCategories] = useState<CategoryData[]>([]);
  const [userStats, setUserStats] = useState<UserStatsData>({ experience: 0, level: "?" });

  const [filter, setFilter] = useState<number | "All">("All");
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [isLoading, setIsLoading] = useState(true);

  const ITEMS_PER_PAGE = 5;

  useEffect(() => {
    api
      .get("/api/auth/me")
      .then((res) => {
        if (res.data && res.data.id) {
          return api.get(`/api/users/${res.data.id}`);
        }
        throw new Error("User me error: ");
      })
      .then((res) => {
        if (res && res.data) {
          setUserStats({
            experience: res.data.experience || 0,
            level: res.data.rankLevel || res.data.rank?.level || res.data.rank?.name || 1,
          });
        }
      })
      .catch((err) => console.error("User detail error: ", err));

    api
      .get("/api/categories")
      .then((res) => {
        if (res.data && res.data.data) {
          setCategories(res.data.data);
        }
      })
      .catch((err) => console.error("Category error: ", err));
  }, []);

  useEffect(() => {
    const params = new URLSearchParams({
      page: currentPage.toString(),
      limit: ITEMS_PER_PAGE.toString(),
    });

    if (filter !== "All") {
      params.append("category", filter.toString());
    }

    api
      .get(`/api/hunts?${params.toString()}`)
      .then((res) => {
        if (res.data) {
          setHunts(res.data.data || []);
          const totalItems = res.data.meta?.total || 0;
          setTotalPages(Math.ceil(totalItems / ITEMS_PER_PAGE) || 1);
        }
      })
      .catch((err) => console.error("Erreur chasses:", err))
      .finally(() => setIsLoading(false));
  }, [currentPage, filter]);

  const handlePageChange = (newPage: number) => {
    if (newPage >= 1 && newPage <= totalPages) {
      setIsLoading(true);
      setCurrentPage(newPage);
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  };

  const handleFilterChange = (categoryId: number | "All") => {
    setIsLoading(true);
    setFilter(categoryId);
    setCurrentPage(1);
  };

  return (
    <div className="hunts-wrapper">
      <div className="topo-overlay"></div>

      <div className="content-container">
        <header className="header-hud">
          <div className="user-status">
            <div className="status-dot"></div>
            <div>
              <div className="radar-status">{t("hunt.radarActive")}</div>
              <div className="sector-label">{t("hunt.sector")}</div>
            </div>
          </div>
          <div className="user-stats-summary">
            <div className="xp-display">
              {userStats.experience.toLocaleString()} {t("hunt.xp")}
            </div>
            <div className="level-display">
              {t("hunt.level")} {userStats.level}
            </div>
          </div>
        </header>

        <div className="page-title-section">
          <h1 className="page-main-title">{t("hunt.title")}</h1>
          <p className="page-subtitle">{t("hunt.subtitle")}</p>
        </div>

        <div className="filter-scroll">
          <button
            className={`filter-pill ${filter === "All" ? "active" : ""}`}
            onClick={() => handleFilterChange("All")}
          >
            {t("hunt.all")}
          </button>
          {categories.map((cat) => (
            <button
              key={cat.id}
              className={`filter-pill ${filter === cat.id ? "active" : ""}`}
              onClick={() => handleFilterChange(cat.id)}
            >
              {cat.name}
            </button>
          ))}
        </div>

        <div className="hunts-list">
          {isLoading ? (
            <div className="empty-search-state">
              <p>{t("hunt.loading")}</p>
            </div>
          ) : hunts.length > 0 ? (
            hunts.map((hunt) => (
              <Link key={hunt.id} className="hunt-card" to={`/radar/${hunt.id}`}>
                <div className="rarity-tag">{hunt.rarity?.name || t("hunt.standardRarity")}</div>

                <div className="hunt-icon">{getBadgeIcon(hunt.category?.icon, 24)}</div>

                <div className="hunt-info">
                  <div className="hunt-merchant">{hunt.company}</div>
                  <h3 className="hunt-title">{hunt.title}</h3>
                  <div className="hunt-meta">
                    <div className="meta-item">
                      <MapPin size={12} />
                      <span>{hunt.location}</span>
                    </div>
                    <div className="meta-item">
                      <Target size={12} />
                      <span>{hunt.reward?.title || t("hunt.mysteryReward")}</span>
                    </div>
                  </div>
                </div>

                <div className="hunt-arrow">
                  <ChevronRight size={24} />
                </div>
              </Link>
            ))
          ) : (
            <div className="empty-search-state">
              <Search size={40} className="empty-search-icon" />
              <p>{t("hunt.empty")}</p>
            </div>
          )}
        </div>

        {totalPages > 1 && (
          <div className="pagination-container">
            <button
              className="page-btn"
              onClick={() => handlePageChange(currentPage - 1)}
              disabled={currentPage === 1}
            >
              <ChevronLeft size={20} />
            </button>

            <div className="page-indicator">
              {t("hunt.page")} {currentPage} / {totalPages}
            </div>

            <button
              className="page-btn"
              onClick={() => handlePageChange(currentPage + 1)}
              disabled={currentPage === totalPages}
            >
              <ChevronRight size={20} />
            </button>
          </div>
        )}
      </div>

      <Navbar activeItem="treasure-hunts" />
    </div>
  );
};

export default TreasureHunt;

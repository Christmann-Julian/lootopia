import { useState } from "react";
import {
  Target,
  Zap,
  Cpu,
  MapPin,
  ChevronLeft,
  ChevronRight,
  Flame,
  Search,
  ShoppingBag,
} from "lucide-react";
import "../assets/css/treasure-hunt.css";
import Navbar from "../components/Navbar";

const TreasureHunt = () => {
  const [filter, setFilter] = useState("All");
  const [currentPage, setCurrentPage] = useState(1);
  const ITEMS_PER_PAGE = 3;

  const hunts = [
    {
      id: 1,
      title: "The Golden Whopper",
      merchant: "BURGER KING",
      reward: "-50% Discount",
      distance: "320m",
      rarity: "Legendary",
      category: "Food",
    },
    {
      id: 2,
      title: "Velocity Sprint",
      merchant: "NIKE STORE",
      reward: "-20% Voucher",
      distance: "1.2km",
      rarity: "Rare",
      category: "Fashion",
    },
    {
      id: 3,
      title: "Neon Caffeine",
      merchant: "STARBUCKS",
      reward: "Free Double Shot",
      distance: "450m",
      rarity: "Common",
      category: "Drink",
    },
    {
      id: 4,
      title: "Tech Fragment",
      merchant: "FNAC",
      reward: "€10 Gift Card",
      distance: "850m",
      rarity: "Rare",
      category: "Tech",
    },
    {
      id: 5,
      title: "Cyber Sneakers",
      merchant: "ADIDAS",
      reward: "-30% Code",
      distance: "2.1km",
      rarity: "Epic",
      category: "Fashion",
    },
    {
      id: 6,
      title: "Pixel Feast",
      merchant: "SUBWAY",
      reward: "Free Cookie",
      distance: "150m",
      rarity: "Common",
      category: "Food",
    },
  ];

  const categories = ["All", "Food", "Fashion", "Tech", "Drink"];
  const filteredHunts = hunts.filter((h) => filter === "All" || h.category === filter);
  const totalPages = Math.ceil(filteredHunts.length / ITEMS_PER_PAGE);
  const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
  const paginatedHunts = filteredHunts.slice(startIndex, startIndex + ITEMS_PER_PAGE);

  const handlePageChange = (newPage: number) => {
    if (newPage >= 1 && newPage <= totalPages) {
      setCurrentPage(newPage);
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  };

  const handleFilterChange = (cat: string) => {
    setFilter(cat);
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
              <div className="radar-status">RADAR_ACTIVE</div>
              <div className="sector-label">PARIS_SECTOR_01</div>
            </div>
          </div>
          <div className="user-stats-summary">
            <div className="xp-display">14,250 XP</div>
            <div className="level-display">LEVEL 14</div>
          </div>
        </header>

        <div className="page-title-section">
          <h1 className="page-main-title">Expeditions</h1>
          <p className="page-subtitle">Select a neural signature to begin tracking.</p>
        </div>

        <div className="filter-scroll">
          {categories.map((cat) => (
            <button
              key={cat}
              className={`filter-pill ${filter === cat ? "active" : ""}`}
              onClick={() => handleFilterChange(cat)}
            >
              {cat}
            </button>
          ))}
        </div>

        <div className="hunts-list">
          {paginatedHunts.length > 0 ? (
            paginatedHunts.map((hunt) => (
              <div key={hunt.id} className="hunt-card">
                <div className="rarity-tag">{hunt.rarity}</div>

                <div className="hunt-icon">
                  {hunt.category === "Food" && <ShoppingBag size={24} />}
                  {hunt.category === "Fashion" && <Zap size={24} />}
                  {hunt.category === "Drink" && <Flame size={24} />}
                  {hunt.category === "Tech" && <Cpu size={24} />}
                </div>

                <div className="hunt-info">
                  <div className="hunt-merchant">{hunt.merchant}</div>
                  <h3 className="hunt-title">{hunt.title}</h3>
                  <div className="hunt-meta">
                    <div className="meta-item">
                      <MapPin size={12} />
                      <span>{hunt.distance}</span>
                    </div>
                    <div className="meta-item">
                      <Target size={12} />
                      <span>{hunt.reward}</span>
                    </div>
                  </div>
                </div>

                <div className="hunt-arrow">
                  <ChevronRight size={24} />
                </div>
              </div>
            ))
          ) : (
            <div className="empty-search-state">
              <Search size={40} className="empty-search-icon" />
              <p>NO SIGNATURES DETECTED</p>
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
              PAGE {currentPage} / {totalPages}
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

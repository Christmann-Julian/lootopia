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

const Reward = () => {
  const username = "Alpha Explorer";

  const rewards = [
    {
      id: 1,
      brand: "BURGER KING",
      reward: "-50% ON MENU",
      code: "WHOPPER-772",
      expiry: "2d 14h",
      category: "Food",
      rarity: "Legendary",
    },
    {
      id: 2,
      brand: "NIKE STORE",
      reward: "-20% RUNNING",
      code: "AIRMAX-2024",
      expiry: "5d 08h",
      category: "Fashion",
      rarity: "Rare",
    },
    {
      id: 3,
      brand: "STARBUCKS",
      reward: "FREE DOUBLE SHOT",
      code: "CAFE-FREE",
      expiry: "Expires tonight",
      category: "Drink",
      rarity: "Common",
    },
  ];

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
              <div className="user-name">{username}</div>
              <div className="inventory-label">REWARDS INVENTORY</div>
            </div>
          </div>
          <div className="ticket-counter">
            <Ticket size={18} color="var(--gold)" />
            <span className="ticket-count">{rewards.length}</span>
          </div>
        </header>

        <div className="title-section">
          <h1 className="page-title">My Rewards</h1>
          <p className="page-subtitle">Extracted loot available for immediate deployment.</p>
        </div>

        <div className="rewards-list">
          {rewards.length > 0 ? (
            rewards.map((item) => (
              <div key={item.id} className="reward-card">
                <div className="ticket-cut cut-top"></div>
                <div className="ticket-cut cut-bottom"></div>

                <div className="reward-main">
                  <div className="rarity-tag">
                    <Zap size={10} fill="currentColor" />
                    {item.rarity}
                  </div>
                  <div className="brand-label">{item.brand}</div>
                  <h3 className="reward-title">{item.reward}</h3>

                  <div className="reward-metadata">
                    <div className="meta-item">
                      <Tag size={12} color="var(--gold)" />
                      <span>{item.category}</span>
                    </div>
                    <div className="meta-item">
                      <Milestone size={12} color="var(--gold)" />
                      <span>CODE: {item.code}</span>
                    </div>
                  </div>
                </div>

                <div className="reward-action">
                  <div className="expiry-timer">
                    <Clock size={16} />
                    <span>{item.expiry}</span>
                  </div>
                  <button className="extract-btn" title="View Reward">
                    <ChevronRight size={26} />
                  </button>
                </div>
              </div>
            ))
          ) : (
            <div className="empty-state">
              <ShoppingBag size={48} className="empty-icon" />
              <h3 className="empty-title">Cargo Empty</h3>
              <p className="empty-text">No active loot detected in your current sector.</p>
            </div>
          )}
        </div>

        <div className="protocol-banner">
          <AlertCircle size={24} color="var(--gold)" className="protocol-icon" />
          <div>
            <span className="protocol-title">Field Protocol</span>
            <p className="protocol-text">
              Each reward code is encrypted and valid for a single extraction. Present this terminal
              to the checkout officer to authorize the discount.
            </p>
          </div>
        </div>
      </div>

      <Navbar activeItem="rewards" />
    </div>
  );
};

export default Reward;

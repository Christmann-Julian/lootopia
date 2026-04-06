import {
  Compass,
  Award,
  Zap,
  Target,
  Flame,
  ShoppingBag,
  Tag,
  MapPin,
  LocateFixed,
  Navigation,
  Milestone,
  Lock,
} from "lucide-react";
import "../assets/css/home.css";
import Navbar from "../components/Navbar";

interface MenuProps {
  username?: string;
  onStartRadar?: () => void;
}

const Home: React.FC<MenuProps> = ({ username = "Explorateur Alpha", onStartRadar }) => {
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
              <span className="username">{username}</span>
              <div className="streak">
                <Flame size={16} fill="currentColor" />
                <span className="streak-text">SÉRIE: 5J</span>
              </div>
            </div>
            <div className="xp-bar">
              <div className="xp-fill"></div>
            </div>
            <div className="xp-info">
              <span>RANG : PIONNIER</span>
              <span>1850 / 2000 XP</span>
            </div>
          </div>
        </header>

        <section className="tactical-card">
          <div className="scan-info">SCAN_LOCAL // SECTEUR_PARIS_01</div>

          <div className="target-info">
            <div>
              <span className="brand-label">
                <Milestone size={10} />
                CIBLE DÉTECTÉE • BURGER KING
              </span>
              <h2 className="loot-title">BOÎTE DE BUTIN : WHOPPER</h2>
            </div>
          </div>

          <div className="reward-info">
            <div className="reward-icon">
              <ShoppingBag size={40} />
            </div>
            <div className="reward-details">
              <div className="reward-text">
                Récompense : <span className="reward-highlight">BON DE RÉDUCTION -50%</span>
              </div>
              <div className="reward-distance">
                <Navigation size={12} fill="currentColor" />
                <span>À 320 MÈTRES DE VOTRE POSITION</span>
              </div>
            </div>
          </div>

          <button className="main-btn" onClick={onStartRadar}>
            <Compass size={22} />
            <span>INITIALISER LE RADAR</span>
          </button>
        </section>

        <div className="sponsored-header">
          <h3>Secteurs sponsorisés</h3>
          <span className="view-all">VOIR TOUT</span>
        </div>

        <div className="sector-grid">
          <div className="sector-card">
            <div className="brand-header">
              <span className="brand-label">NIKE STORE</span>
              <span className="reward-pill">-20%</span>
            </div>
            <h4 className="sector-title">
              <Target size={20} color="var(--gold)" />
              Air Max Expedition
            </h4>
            <p className="sector-desc">
              Localisez le code d'accès dissimulé dans le rayon running.
            </p>
            <div className="sector-footer">
              <MapPin size={12} />
              <span>SECTEUR : CENTRE-VILLE</span>
            </div>
          </div>

          <div className="sector-card">
            <div className="brand-header">
              <span className="brand-label">STARBUCKS</span>
              <span className="reward-pill" style={{ background: "#7c3aed" }}>
                OFFERT
              </span>
            </div>
            <h4 className="sector-title">
              <Zap size={20} color="var(--gold)" />
              Double Shot Hunt
            </h4>
            <p className="sector-desc">Challenge d'endurance : Parcourez 600m pour votre café.</p>
            <div className="sector-footer">
              <MapPin size={12} />
              <span>SECTEUR : CENTRE-VILLE</span>
            </div>
          </div>

          <div className="sector-card">
            <div className="brand-header">
              <span className="brand-label">FNAC</span>
              <span className="reward-pill" style={{ background: "#3b82f6" }}>
                10€
              </span>
            </div>
            <h4 className="sector-title">
              <Tag size={20} color="var(--gold)" />
              Rayon Culturel
            </h4>
            <p className="sector-desc">Explorez le secteur technologie et scannez l'anomalie.</p>
            <div className="sector-footer">
              <MapPin size={12} />
              <span>SECTEUR : CENTRE-VILLE</span>
            </div>
          </div>

          <div className="sector-card locked">
            <div className="brand-header">
              <span className="brand-label">DECATHLON</span>
              <span className="reward-pill" style={{ background: "#4b5563" }}>
                BLOQUÉ
              </span>
            </div>
            <h4 className="sector-title">
              <Lock size={20} color="#4b5563" />
              Secteur Rando
            </h4>
            <p className="sector-desc">Requiert le niveau 15 pour débloquer cette expédition.</p>
            <div className="sector-footer locked-footer">
              <Award size={12} />
              <span>NIVEAU REQUIS : 15</span>
            </div>
          </div>
        </div>
      </div>

      <Navbar activeItem="home" />
    </div>
  );
};

export default Home;

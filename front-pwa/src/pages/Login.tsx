import { Compass, User, Key, Fingerprint } from "lucide-react";
import "../assets/css/login.css";

const Login: React.FC = () => {
  return (
    <div className="login-wrapper">
      <div className="topo-overlay"></div>

      <div className="map-coords map-coords-top">
        <span>48.8566°N // 2.3522°E</span>
        <span>NAV_SYS: ACTIVE</span>
      </div>

      <div className="map-coords map-coords-left">
        <span>01</span>
        <span>02</span>
        <span>03</span>
        <span>04</span>
      </div>

      <Compass size={400} className="compass-bg" />
      <div className="login-card">
        <div className="logo-box">
          <div className="logo-hex">
            <Compass size={44} className="animate-pulse-gold" />
          </div>
        </div>

        <h1 className="brand-name">Lootopia</h1>
        <p className="tagline">Expédition Tactique</p>

        <div className="form-container">
          <div className="container-relative">
            <input type="text" placeholder="Email ou Pseudo" className="tactical-input" />
            <User className="input-icon" size={18} />
          </div>

          <div className="container-relative">
            <input type="password" placeholder="••••••••" className="tactical-input" />
            <Key className="input-icon" size={18} />
          </div>

          <button className="action-btn">
            <Fingerprint size={22} />
            <span>S'IDENTIFIER</span>
          </button>
          <p className="register-link">
            Pas de profil ? <span>S'inscrire</span>
          </p>
        </div>
      </div>
    </div>
  );
};

export default Login;

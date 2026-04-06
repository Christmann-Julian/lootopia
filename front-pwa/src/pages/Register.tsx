import React, { useState } from "react";
import { Compass, User, Mail, Lock, ShieldCheck, Fingerprint, Shield } from "lucide-react";
import "../assets/css/register.css";

const Register = () => {
  const [formData, setFormData] = useState({
    lastName: "",
    firstName: "",
    username: "",
    email: "",
    password: "",
    confirmPassword: "",
  });

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setFormData({ ...formData, [e.target.name]: e.target.value });
  };

  return (
    <div className="register-wrapper">
      <div className="topo-overlay"></div>
      <Compass size={450} className="compass-bg" />

      <div className="register-card">
        <header className="register-header">
          <div className="header-icon-box">
            <Fingerprint size={32} />
          </div>
          <h1 className="register-title">Nouvelle Recrue</h1>
          <p className="register-subtitle">Initialisation du profil neuronal</p>
        </header>

        <form className="register-form">
          <div className="form-grid">
            <div className="input-wrap">
              <label className="input-label">Prénom</label>
              <input
                type="text"
                name="firstName"
                placeholder="Jean"
                className="tactical-input"
                required
                onChange={handleChange}
              />
              <User size={16} className="field-icon" />
            </div>
            <div className="input-wrap">
              <label className="input-label">Nom</label>
              <input
                type="text"
                name="lastName"
                placeholder="Dupont"
                className="tactical-input"
                required
                onChange={handleChange}
              />
              <User size={16} className="field-icon" />
            </div>
          </div>

          <div className="input-wrap form-group">
            <label className="input-label">Pseudo</label>
            <input
              type="text"
              name="username"
              placeholder="Explorateur_Alpha"
              className="tactical-input"
              required
              onChange={handleChange}
            />
            <Fingerprint size={16} className="field-icon" />
          </div>

          <div className="input-wrap form-group">
            <label className="input-label">Email</label>
            <input
              type="email"
              name="email"
              placeholder="nexus@lootopia.net"
              className="tactical-input"
              required
              onChange={handleChange}
            />
            <Mail size={16} className="field-icon" />
          </div>

          <div className="input-wrap form-group">
            <label className="input-label">Mot de passe</label>
            <input
              type="password"
              name="password"
              placeholder="••••••••"
              className="tactical-input"
              required
              onChange={handleChange}
            />
            <Lock size={16} className="field-icon" />
          </div>

          <div className="input-wrap form-group">
            <label className="input-label">Confirmer le Mot de passe</label>
            <input
              type="password"
              name="confirmPassword"
              placeholder="••••••••"
              className="tactical-input"
              required
              onChange={handleChange}
            />
            <ShieldCheck size={16} className="field-icon" />
          </div>

          <button type="submit" className="action-btn">
            <Shield size={20} />
            <span>Créer le Profil</span>
          </button>

          <p className="login-redirect">
            Déjà un profil ? <span className="login-link">S'identifier</span>
          </p>
        </form>
      </div>
    </div>
  );
};

export default Register;

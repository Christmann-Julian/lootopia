import { useState } from "react";
import { Compass, User, Key, Fingerprint } from "lucide-react";
import { useTranslation } from "react-i18next";
import { useForm, type SubmitHandler } from "react-hook-form";
import { api, setAccessToken } from "../services/auth";
import { Link, useNavigate } from "react-router-dom";
import type { AxiosError } from "axios";
import Toast from "../components/Toast";
import "../assets/css/login.css";

const Login: React.FC = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);
  const { register, handleSubmit } = useForm<{ email: string; password: string }>();

  const onSubmit: SubmitHandler<{ email: string; password: string }> = async (data) => {
    setToast(null);

    try {
      const response = await api.post("/api/auth/login", data);
      setAccessToken(response.data.token);

      setToast({
        message: t("login.success"),
        type: "success",
      });

      navigate("/home");
    } catch (error: unknown) {
      const axiosError = error as AxiosError;
      if (axiosError.response && axiosError.response.status === 401) {
        setToast({
          message: t("error.invalidCredentials"),
          type: "error",
        });
      } else {
        setToast({
          message: t("error.serverError"),
          type: "error",
        });
      }
    }
  };

  return (
    <div className="login-wrapper">
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}

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

        <h1 className="brand-name">{t("login.title")}</h1>
        <p className="tagline">{t("login.subtitle")}</p>

        <form className="form-container" onSubmit={handleSubmit(onSubmit)}>
          <div className="container-relative">
            <input
              type="text"
              placeholder={t("login.emailPlaceholder")}
              className="tactical-input"
              {...register("email", { required: t("error.requiredField") })}
            />
            <User className="input-icon" size={18} />
          </div>

          <div className="container-relative">
            <input
              type="password"
              placeholder={t("login.passwordPlaceholder")}
              className="tactical-input"
              {...register("password", { required: t("error.requiredField") })}
            />
            <Key className="input-icon" size={18} />
          </div>

          <button className="action-btn" type="submit">
            <Fingerprint size={22} />
            <span>{t("login.submit")}</span>
          </button>
          <p className="register-link">
            {t("login.noAccount")} <Link to="/register">{t("login.register")}</Link>
          </p>
        </form>
      </div>
    </div>
  );
};

export default Login;

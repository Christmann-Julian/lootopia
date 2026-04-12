import { useState } from "react";
import { useForm, type SubmitHandler } from "react-hook-form";
import { Compass, User, Mail, Lock, ShieldCheck, Fingerprint, Shield } from "lucide-react";
import "../assets/css/register.css";
import { useTranslation } from "react-i18next";
import Toast from "../components/Toast";
import type { RegisterFormInputs } from "../types/FormType";
import { Link } from "react-router-dom";

const Register = () => {
  const {
    register,
    handleSubmit,
    formState: { errors },
    getValues,
    setError,
    reset,
  } = useForm<RegisterFormInputs>();

  const { t } = useTranslation();
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);

  const onSubmit: SubmitHandler<RegisterFormInputs> = async (data) => {
    setToast(null);

    try {
      const response = await fetch("https://localhost:8000/api/auth/register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      });

      if (!response.ok) {
        const errorData = await response.json();

        setToast({
          message: errorData.message || t("error.serverError"),
          type: "error",
        });

        if (errorData.details) {
          Object.keys(errorData.details).forEach((field) => {
            const fieldName = field as keyof RegisterFormInputs;
            const errorMessage = errorData.details[field][0];

            setError(fieldName, {
              type: "error",
              message: errorMessage,
            });
          });
        }
        return;
      }

      setToast({
        message: t("register.success"),
        type: "success",
      });

      reset();
    } catch {
      setToast({
        message: t("error.serverError"),
        type: "error",
      });
    }
  };

  return (
    <div className="register-wrapper">
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}

      <div className="topo-overlay"></div>
      <Compass size={450} className="compass-bg" />

      <div className="register-card">
        <header className="register-header">
          <div className="header-icon-box">
            <Fingerprint size={32} />
          </div>
          <h1 className="register-title">{t("register.title")}</h1>
          <p className="register-subtitle">{t("register.subtitle")}</p>
        </header>

        <form className="register-form" onSubmit={handleSubmit(onSubmit)}>
          <div className="form-grid">
            <div className="input-wrap">
              <label className="input-label">{t("input.firstName")}</label>
              <input
                type="text"
                {...register("firstname", {
                  required: t("error.requiredField"),
                  minLength: {
                    value: 2,
                    message: t("error.lengthConstraint", { min: 2, max: 100 }),
                  },
                  maxLength: {
                    value: 100,
                    message: t("error.lengthConstraint", { min: 2, max: 100 }),
                  },
                })}
                placeholder={t("input.firstName")}
                className="tactical-input"
              />
              {errors.firstname && (
                <span className="error-message">{errors.firstname.message}</span>
              )}
              <User size={16} className="field-icon" />
            </div>
            <div className="input-wrap">
              <label className="input-label">{t("input.lastName")}</label>
              <input
                type="text"
                {...register("lastname", {
                  required: t("error.requiredField"),
                  minLength: {
                    value: 2,
                    message: t("error.lengthConstraint", { min: 2, max: 100 }),
                  },
                  maxLength: {
                    value: 100,
                    message: t("error.lengthConstraint", { min: 2, max: 100 }),
                  },
                })}
                placeholder={t("input.lastName")}
                className="tactical-input"
              />
              {errors.lastname && <span className="error-message">{errors.lastname.message}</span>}
              <User size={16} className="field-icon" />
            </div>
          </div>

          <div className="input-wrap form-group">
            <label className="input-label">{t("input.pseudo")}</label>
            <input
              type="text"
              {...register("pseudo", { required: t("error.requiredField") })}
              placeholder={t("input.pseudo")}
              className="tactical-input"
            />
            {errors.pseudo && <span className="error-message">{errors.pseudo.message}</span>}
            <Fingerprint size={16} className="field-icon" />
          </div>

          <div className="input-wrap form-group">
            <label className="input-label">{t("input.email")}</label>
            <input
              type="email"
              {...register("email", {
                required: t("error.requiredField"),
                pattern: {
                  value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                  message: t("error.invalidEmail"),
                },
              })}
              placeholder={t("input.email")}
              className="tactical-input"
            />
            {errors.email && <span className="error-message">{errors.email.message}</span>}
            <Mail size={16} className="field-icon" />
          </div>

          <div className="input-wrap form-group">
            <label className="input-label">{t("input.password")}</label>
            <input
              type="password"
              {...register("password", {
                required: t("error.requiredField"),
                minLength: {
                  value: 10,
                  message: t("error.lengthConstraint", { min: 10, max: 255 }),
                },
                maxLength: {
                  value: 255,
                  message: t("error.lengthConstraint", { min: 10, max: 255 }),
                },
              })}
              placeholder={t("input.password")}
              className="tactical-input"
            />
            {errors.password && <span className="error-message">{errors.password.message}</span>}
            <Lock size={16} className="field-icon" />
          </div>

          <div className="input-wrap form-group">
            <label className="input-label">{t("input.confirmPassword")}</label>
            <input
              type="password"
              {...register("confirmPassword", {
                required: t("error.requiredField"),
                validate: (value) =>
                  value === getValues("password") || t("error.passwordsDoNotMatch"),
              })}
              placeholder={t("input.confirmPassword")}
              className="tactical-input"
            />
            {errors.confirmPassword && (
              <span className="error-message">{errors.confirmPassword.message}</span>
            )}
            <ShieldCheck size={16} className="field-icon" />
          </div>

          <button type="submit" className="action-btn">
            <Shield size={20} />
            <span>{t("register.submit")}</span>
          </button>

          <p className="login-redirect">
            {t("register.alreadyHaveAccount")}{" "}
            <Link to="/login" className="login-link">
              {t("register.login")}
            </Link>
          </p>
        </form>
      </div>
    </div>
  );
};

export default Register;

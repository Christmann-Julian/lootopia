import { useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import { useForm, type SubmitHandler } from "react-hook-form";
import { Link, useFetcher, type ClientActionFunctionArgs } from "react-router";
import { getLanguage } from "../../utils/i18nUtils";
import Toast from "../../components/Toast";
import { api } from "../../services/auth/auth";
import type { ForgotPasswordFormData } from "../../types/FormType";
import "../../assets/css/forgot-password.css";

export async function clientAction({ request }: ClientActionFunctionArgs) {
  const data = await request.json();
  try {
    await api.post("/api/auth/password/reset/request", data);
    return { success: true };
  } catch (err: any) {
    return { error: err.response.data.message };
  }
}

export function meta() {
  const { t } = useTranslation("auth");
  return [{ title: t("forgotPassword.metaTitle", { ns: "auth" }) }];
}

export default function ForgotPassword() {
  const { t } = useTranslation(["auth", "validation", "common"]);
  const fetcher = useFetcher();
  const [toast, setToast] = useState<{
    message: string;
    type: "success" | "error" | "info" | "warning";
  } | null>(null);

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<ForgotPasswordFormData>();

  const onSubmit: SubmitHandler<ForgotPasswordFormData> = (data) => {
    fetcher.submit(data, {
      method: "post",
      encType: "application/json",
    });
    reset();
  };

  useEffect(() => {
    if (fetcher.data?.success) {
      setToast({
        message: t("forgotPassword.successMessage", { ns: "auth" }),
        type: "success",
      });
    } else if (fetcher.data?.error) {
      setToast({ message: t("internalServerError", { ns: "common" }), type: "error" });
    }
  }, [fetcher.data, t]);

  return (
    <div className="forgot-container">
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}
      <div className="card">
        <div className="card-header">
          <div className="icon-wrapper">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="16" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
          </div>
          <h1 className="card-title">{t("forgotPassword.title", { ns: "auth" })}</h1>
          <p className="card-description">{t("forgotPassword.description", { ns: "auth" })}</p>
        </div>

        <form onSubmit={handleSubmit(onSubmit)}>
          <div className="form-group">
            <label className="label" htmlFor="email">
              {t("forgotPassword.email", { ns: "auth" })}
            </label>
            <div className="input-wrapper">
              <svg
                className="input-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
              <input
                type="email"
                id="email"
                className="input input-with-icon"
                placeholder={t("forgotPassword.emailPlaceholder", { ns: "auth" })}
                {...register("email", {
                  required: t("required", { ns: "validation" }),
                  pattern: {
                    value: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                    message: t("invalidEmail", { ns: "validation" }),
                  },
                })}
              />
            </div>
            {errors.email && <div className="input-feedback error">{errors.email.message}</div>}
            <p className="helper-text">{t("forgotPassword.helpText", { ns: "auth" })}</p>
          </div>

          <button
            type="submit"
            className="button button-primary"
            disabled={fetcher.state === "submitting"}
          >
            {fetcher.state === "submitting" ? (
              <svg
                className="spinner-btn"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <circle cx="12" cy="12" r="10" strokeOpacity="0.25" />
                <path d="M12 2a10 10 0 0 1 10 10" />
              </svg>
            ) : (
              <svg
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
            )}
            {t("forgotPassword.resetButton", { ns: "auth" })}
          </button>
        </form>

        <div className="info-box" style={{ marginTop: "20px" }}>
          <div className="info-box-title">
            💡 {t("forgotPassword.infoBox.title", { ns: "auth" })}
          </div>
          {t("forgotPassword.infoBox.message", { ns: "auth" })}
        </div>

        <div className="card-footer">
          <p className="footer-text">
            <Link to={`/${getLanguage()}`} className="link">
              <svg
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
              </svg>
              {t("forgotPassword.backToLogin", { ns: "auth" })}
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}

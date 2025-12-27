import { useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import { useForm, type SubmitHandler } from "react-hook-form";
import {
  Link,
  useNavigate,
  useSearchParams,
  useFetcher,
  type ClientActionFunctionArgs,
} from "react-router";
import { getLanguage } from "../../utils/i18nUtils";
import Toast from "../../components/Toast";
import { api } from "../../services/auth/auth";
import type { ResetPasswordFormData } from "../../types/FormType";
import type { ApiErrorResponse } from "../../types/ApiType";
import "../../assets/css/reset-password.css";

export async function clientAction({ request }: ClientActionFunctionArgs) {
  const data = await request.json();

  try {
    await api.post("/api/auth/password/reset", data);
    return { success: true };
  } catch (err: any) {
    const apiError = err.response?.data as ApiErrorResponse;
    if (apiError?.details) {
      const firstError = Object.values(apiError.details)[0];
      return { error: firstError?.[0] || apiError.message };
    }
    return { error: apiError.message };
  }
}

export function meta() {
  const { t } = useTranslation("auth");
  return [{ title: t("resetPassword.metaTitle", { ns: "auth" }) }];
}

export default function ResetPassword() {
  const { t } = useTranslation(["auth", "validation", "common"]);
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const fetcher = useFetcher();
  const [passwordStrength, setPasswordStrength] = useState<string>("");
  const [passwordVisible, setPasswordVisible] = useState<boolean>(false);
  const [toast, setToast] = useState<{
    message: string;
    type: "success" | "error" | "info" | "warning";
  } | null>(null);

  const token = searchParams.get("token");
  const email = searchParams.get("email");

  const {
    register,
    handleSubmit,
    getValues,
    reset,
    formState: { errors },
  } = useForm<ResetPasswordFormData>();

  useEffect(() => {
    if (fetcher.data?.success) {
      setToast({
        message: t("resetPassword.successMessage", { ns: "auth" }),
        type: "success",
      });
      setTimeout(() => {
        navigate(`/${getLanguage()}`);
      }, 2000);
    } else if (fetcher.data?.error) {
      setToast({
        message:
          fetcher.data.error === true
            ? t("internalServerError", { ns: "common" })
            : fetcher.data.error,
        type: "error",
      });
    }
  }, [fetcher.data, navigate, t]);

  const onSubmit: SubmitHandler<ResetPasswordFormData> = (data) => {
    if (!token) {
      setToast({
        message: t("resetPassword.invalidLinkMessage", { ns: "auth" }),
        type: "error",
      });
      return;
    }

    fetcher.submit(
      { ...data, token },
      {
        method: "post",
        encType: "application/json",
      }
    );
    reset();
  };

  const evaluatePasswordStrength = (password: string) => {
    let strength = 0;

    if (password.length >= 10) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    if (strength <= 2) return "weak";
    if (strength === 3 || strength === 4) return "medium";
    return "strong";
  };

  const togglePasswordVisibility = () => {
    setPasswordVisible((prev) => !prev);
  };

  return (
    <div className="reset-container">
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}
      <div className="card">
        <div className="card-header">
          <div className="icon-wrapper">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
          </div>
          <h1 className="card-title">{t("resetPassword.title", { ns: "auth" })}</h1>
          <p className="card-description">
            {t("resetPassword.description", { ns: "auth" })}
            {email && <span> ({email})</span>}
          </p>
        </div>

        <form onSubmit={handleSubmit(onSubmit)}>
          <div className="form-group">
            <label className="label" htmlFor="password">
              {t("resetPassword.newPassword", { ns: "auth" })}
            </label>
            <div className="input-wrapper">
              <svg
                className="input-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
              <input
                type={passwordVisible ? "text" : "password"}
                id="password"
                className="input input-with-icon"
                placeholder="••••••••"
                {...register("password", {
                  required: t("required", { ns: "validation" }),
                  minLength: {
                    value: 10,
                    message: t("minLength", { min: 10, ns: "validation" }),
                  },
                  maxLength: {
                    value: 250,
                    message: t("maxLength", { max: 250, ns: "validation" }),
                  },
                  onChange: (e) => {
                    const strength = evaluatePasswordStrength(e.target.value);
                    setPasswordStrength(strength);
                  },
                })}
              />
              <svg
                className="password-toggle"
                onClick={togglePasswordVisibility}
                id="togglePassword"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </div>
            {errors.password && (
              <div className="input-feedback error">{errors.password.message}</div>
            )}
            {passwordStrength && (
              <div className="password-strength">
                <div className="strength-bar">
                  <div className={`strength-fill ${passwordStrength}`} id="strengthFill"></div>
                </div>
                <div className="strength-text" id="strengthText">
                  {t("passwordStrength", {
                    strength: t(passwordStrength, { ns: "validation" }),
                    ns: "validation",
                  })}
                </div>
              </div>
            )}
          </div>

          <div className="form-group">
            <label className="label" htmlFor="confirmPassword">
              {t("resetPassword.confirmNewPassword", { ns: "auth" })}
            </label>
            <div className="input-wrapper">
              <svg
                className="input-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
              <input
                type={passwordVisible ? "text" : "password"}
                id="confirmPassword"
                className="input input-with-icon"
                placeholder="••••••••"
                {...register("confirmPassword", {
                  required: t("required", { ns: "validation" }),
                  validate: (value) =>
                    value === getValues("password") ||
                    t("passwordsDoNotMatch", { ns: "validation" }),
                })}
              />
              <svg
                className="password-toggle"
                onClick={togglePasswordVisibility}
                id="toggleConfirmPassword"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </div>
            {errors.confirmPassword && (
              <div className="input-feedback error" id="confirmPasswordError">
                {errors.confirmPassword.message}
              </div>
            )}
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
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            )}
            {t("resetPassword.resetButton", { ns: "auth" })}
          </button>
        </form>

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
              {t("resetPassword.backToLogin", { ns: "auth" })}
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}

import { useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import { useFetcher, useParams } from "react-router";
import { useForm, type SubmitHandler } from "react-hook-form";
import type { ChangePasswordFormData } from "../types/FormType";
import Toast from "./Toast";

type ChangePasswordProps = {
  userId: number | null;
};

export default function ChangePassword({ userId }: ChangePasswordProps) {
  const { lang } = useParams();
  const fetcher = useFetcher();
  const { t } = useTranslation(["form", "validation"]);
  const [passwordStrength, setPasswordStrength] = useState<string>("");
  const [passwordVisible, setPasswordVisible] = useState<boolean>(false);
  const [toast, setToast] = useState<{
    message: string;
    type: "success" | "error" | "info" | "warning";
  } | null>(null);

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<ChangePasswordFormData>();

  const onSubmitChangePassword: SubmitHandler<ChangePasswordFormData> = (
    data: ChangePasswordFormData
  ) => {
    const dataWithId = {
      ...data,
      id: userId,
    };

    fetcher.submit(dataWithId, {
      method: "post",
      encType: "application/json",
      action: `/${lang}/action/change-password`,
    });
  };

  useEffect(() => {
    if (fetcher.data?.success) {
      reset();
      setPasswordStrength("");
      setPasswordVisible(false);
      setToast({
        message: t("toast.passwordChanged", { ns: "form" }),
        type: "success",
      });
    } else if (fetcher.data?.error) {
      setToast({
        message:
          fetcher.data.error === true
            ? t("internalServerError", { ns: "common" })
            : fetcher.data.error,
        type: "error",
      });
    }
  }, [fetcher.data, t, reset]);

  const evaluatePasswordStrength = (password: string): string => {
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

  const togglePasswordVisibility = (): void => {
    setPasswordVisible((prev) => !prev);
  };

  return (
    <>
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}
      <form className="form-container" onSubmit={handleSubmit(onSubmitChangePassword)}>
        <div className="card">
          <div className="card-header">
            <h2 className="card-title">{t("title.changePassword", { ns: "form" })}</h2>
            <p className="card-description">{t("description.changePassword", { ns: "form" })}</p>
          </div>
          <div className="card-content">
            <div className="form-group">
              <label className="label label-required" htmlFor="currentPassword">
                {t("currentPassword", { ns: "form" })}
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
                  id="currentPassword"
                  className="input input-with-icon"
                  placeholder="••••••••"
                  {...register("currentPassword", {
                    required: t("required", { ns: "validation" }),
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
              {errors.currentPassword && (
                <div className="input-feedback error" id="passwordError">
                  {errors.currentPassword.message}
                </div>
              )}
            </div>
            <div className="form-group">
              <label className="label label-required" htmlFor="newPassword">
                {t("newPassword", { ns: "form" })}
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
                  id="newPassword"
                  className="input input-with-icon"
                  placeholder="••••••••"
                  {...register("newPassword", {
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
                  id="togglePassword"
                  onClick={togglePasswordVisibility}
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                >
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </div>
              {errors.newPassword && (
                <div className="input-feedback error" id="passwordError">
                  {errors.newPassword.message}
                </div>
              )}
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
            </div>
          </div>
          <div className="form-actions">
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
              {t("save", { ns: "form" })}
            </button>
          </div>
        </div>
      </form>
    </>
  );
}

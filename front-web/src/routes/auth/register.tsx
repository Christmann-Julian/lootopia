import { useState, useEffect } from "react";
import { useTranslation, Trans } from "react-i18next";
import { useForm, type SubmitHandler } from "react-hook-form";
import type { RegisterFormData } from "../../types/FormType";
import type { ApiErrorResponse } from "../../types/ApiType";
import Toast from "../../components/Toast";
import {
  Link,
  useNavigate,
  useFetcher,
  type MetaFunction,
  type LinksFunction,
  useParams,
  type ClientActionFunctionArgs,
} from "react-router";
import i18n from "i18next";
import { api } from "../../services/auth";
import type { AxiosError } from "axios";

export async function clientAction({ request }: ClientActionFunctionArgs) {
  const data = await request.json();
  try {
    await api.post("/api/auth/register", data);
    return { success: true };
  } catch (err: unknown) {
    const axiosError = err as AxiosError<ApiErrorResponse>;
    const apiError = axiosError.response?.data;

    if (apiError?.details) {
      const firstError = Object.values(apiError.details)[0];
      return { error: firstError?.[0] || true };
    }

    return { error: true };
  }
}

export const meta: MetaFunction = () => [{ title: i18n.t("register.metaTitle", { ns: "auth" }) }];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/register.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
];

export default function Register() {
  const { lang } = useParams();
  const [passwordStrength, setPasswordStrength] = useState<string>("");
  const [passwordVisible, setPasswordVisible] = useState<boolean>(false);
  const [toast, setToast] = useState<{
    message: string;
    type: "success" | "error" | "info" | "warning";
  } | null>(null);
  const { t } = useTranslation(["auth", "validation", "common"]);
  const navigate = useNavigate();
  const fetcher = useFetcher();

  const {
    register,
    handleSubmit,
    getValues,
    formState: { errors },
  } = useForm<RegisterFormData>();

  const onSubmit: SubmitHandler<RegisterFormData> = (data: RegisterFormData) => {
    fetcher.submit(data, {
      method: "post",
      encType: "application/json",
    });
  };

  useEffect(() => {
    if (fetcher.data?.success) {
      navigate(`/${lang}/register-success`);
    } else if (fetcher.data?.error) {
      setToast({
        message:
          fetcher.data.error === true
            ? t("internalServerError", { ns: "common" })
            : fetcher.data.error,
        type: "error",
      });
    }
  }, [fetcher.data, t, navigate, lang]);

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
    <div className="register-container">
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}
      <div className="logo">
        <img
          src="/assets/images/logo_circle_256x256.png"
          width={48}
          height={48}
          alt="Lootopia Logo"
        />
        <span className="logo-text">{t("appName", { ns: "common" })}</span>
      </div>

      <div className="card">
        <div className="card-header">
          <h1 className="card-title">{t("register.title")}</h1>
          <p className="card-description">{t("register.subtitle")}</p>
        </div>

        <form id="registerForm" onSubmit={handleSubmit(onSubmit)}>
          <div className="form-row">
            <div className="form-group">
              <label className="label" htmlFor="firstname">
                {t("register.firstName")}
              </label>
              <div className="input-wrapper">
                <svg
                  className="input-icon"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                >
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <input
                  type="text"
                  id="firstname"
                  className="input input-with-icon"
                  placeholder={t("register.firstNamePlaceholder")}
                  {...register("firstname", {
                    required: t("required", { ns: "validation" }),
                    minLength: {
                      value: 2,
                      message: t("minLength", { min: 2, ns: "validation" }),
                    },
                    maxLength: {
                      value: 100,
                      message: t("maxLength", { max: 100, ns: "validation" }),
                    },
                  })}
                />
              </div>
              {errors.firstname && (
                <div className="input-feedback error" id="firstnameError">
                  {errors.firstname.message}
                </div>
              )}
            </div>

            <div className="form-group">
              <label className="label" htmlFor="lastname">
                {t("register.lastName")}
              </label>
              <div className="input-wrapper">
                <svg
                  className="input-icon"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                >
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <input
                  type="text"
                  id="lastname"
                  className="input input-with-icon"
                  placeholder={t("register.lastNamePlaceholder")}
                  {...register("lastname", {
                    required: t("required", { ns: "validation" }),
                    minLength: {
                      value: 2,
                      message: t("minLength", { min: 2, ns: "validation" }),
                    },
                    maxLength: {
                      value: 100,
                      message: t("maxLength", { max: 100, ns: "validation" }),
                    },
                  })}
                />
              </div>
              {errors.lastname && (
                <div className="input-feedback error" id="lastnameError">
                  {errors.lastname.message}
                </div>
              )}
            </div>
          </div>

          <div className="form-group">
            <label className="label" htmlFor="pseudo">
              {t("register.pseudo")}
            </label>
            <div className="input-wrapper">
              <svg
                className="input-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
              <input
                type="text"
                id="pseudo"
                className="input input-with-icon"
                placeholder={t("register.pseudoPlaceholder")}
                {...register("pseudo", {
                  required: t("required", { ns: "validation" }),
                  minLength: {
                    value: 2,
                    message: t("minLength", { min: 2, ns: "validation" }),
                  },
                  maxLength: {
                    value: 100,
                    message: t("maxLength", { max: 100, ns: "validation" }),
                  },
                })}
              />
            </div>
            {errors.pseudo && (
              <div className="input-feedback error" id="pseudoError">
                {errors.pseudo.message}
              </div>
            )}
          </div>

          <div className="form-group">
            <label className="label" htmlFor="email">
              {t("register.email")}
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
                placeholder={t("register.emailPlaceholder")}
                {...register("email", {
                  required: t("required", { ns: "validation" }),
                  pattern: {
                    value: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                    message: t("invalidEmail", { ns: "validation" }),
                  },
                })}
              />
            </div>
            {errors.email && (
              <div className="input-feedback error" id="emailError">
                {errors.email.message}
              </div>
            )}
          </div>

          <div className="form-group">
            <label className="label" htmlFor="company">
              {t("register.company")}
            </label>
            <div className="input-wrapper">
              <svg
                className="input-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
              </svg>
              <input
                type="text"
                id="company"
                className="input input-with-icon"
                placeholder={t("register.companyPlaceholder")}
                {...register("company", {
                  required: t("required", { ns: "validation" }),
                  maxLength: {
                    value: 255,
                    message: t("maxLength", { max: 255, ns: "validation" }),
                  },
                })}
              />
            </div>
            {errors.company && (
              <div className="input-feedback error" id="companyError">
                {errors.company.message}
              </div>
            )}
          </div>

          <div className="form-group">
            <label className="label" htmlFor="password">
              {t("register.password")}
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
              <div className="input-feedback error" id="passwordError">
                {errors.password.message}
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

          <div className="form-group">
            <label className="label" htmlFor="confirmPassword">
              {t("register.confirmPassword")}
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

          <div className="checkbox-wrapper">
            <input
              type="checkbox"
              id="terms"
              className="checkbox"
              {...register("terms", { required: t("required", { ns: "validation" }) })}
            />
            <div className="terms-container">
              <label htmlFor="terms" className="checkbox-label">
                <Trans
                  i18nKey="register.termsAndConditions"
                  ns="auth"
                  components={{
                    1: <a href="#" className="link" />,
                    3: <a href="#" className="link" />,
                  }}
                />
              </label>
              {errors.terms && (
                <div className="input-feedback error" id="termsError">
                  {errors.terms.message}
                </div>
              )}
            </div>
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
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <line x1="20" y1="8" x2="20" y2="14"></line>
                <line x1="23" y1="11" x2="17" y2="11"></line>
              </svg>
            )}
            {t("register.registerButton")}
          </button>
        </form>

        <div className="card-footer">
          <p className="footer-text">
            {t("register.alreadyHaveAccount")} &nbsp;
            <Link to={`/${lang}`} className="link">
              {t("register.loginHere")}
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}

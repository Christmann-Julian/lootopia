import { useState, useEffect } from "react";
import Toast from "../../../../components/Toast";
import SideBar from "../../../../components/SideBar";
import DashboardHeader from "../../../../components/DashboardHeader";
import { useTranslation } from "react-i18next";
import {
  useFetcher,
  useParams,
  type MetaFunction,
  type LinksFunction,
  type ClientActionFunctionArgs,
  Link,
} from "react-router";
import { useForm, type SubmitHandler } from "react-hook-form";
import i18n from "i18next";
import type { CreateUserFormData } from "../../../../types/FormType";
import type { ApiErrorResponse } from "../../../../types/ApiType";
import { api } from "../../../../services/auth";
import type { AxiosError } from "axios";

export async function clientAction({ request }: ClientActionFunctionArgs) {
  const data = await request.json();

  try {
    await api.post(`/api/users`, data);
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

export const meta: MetaFunction = () => [
  { title: i18n.t("metaTitle", { title: i18n.t("users", { ns: "navigation" }), ns: "common" }) },
];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/ui/dashboard-header.css" },
  { rel: "stylesheet", href: "/assets/css/ui/sidebar.css" },
  { rel: "stylesheet", href: "/assets/css/ui/button.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
  { rel: "stylesheet", href: "/assets/css/ui/form.css" },
];

export default function UserCreate() {
  const { t } = useTranslation(["form", "validation", "navigation", "common"]);
  const { lang } = useParams();
  const fetcher = useFetcher();
  const [passwordStrength, setPasswordStrength] = useState<string>("");
  const [passwordVisible, setPasswordVisible] = useState<boolean>(false);
  const [toast, setToast] = useState<{
    message: string;
    type: "success" | "error" | "info" | "warning";
  } | null>(null);
  const [tags, setTags] = useState([
    { value: "ROLE_USER", label: "ROLE_USER", active: true },
    { value: "ROLE_ADMIN", label: "ROLE_ADMIN", active: false },
  ]);

  const {
    register,
    handleSubmit,
    getValues,
    setValue,
    reset,
    formState: { errors },
  } = useForm<CreateUserFormData>();

  const toggleTag = (value: string) => {
    setTags((prevTags) =>
      prevTags.map((tag) => (tag.value === value ? { ...tag, active: !tag.active } : tag))
    );
  };

  useEffect(() => {
    const selectedRoles = tags.filter((tag) => tag.active).map((tag) => tag.value);
    setValue("roles", selectedRoles);
  }, [tags, setValue]);

  const onSubmit: SubmitHandler<CreateUserFormData> = (data: CreateUserFormData) => {
    fetcher.submit(data, {
      method: "post",
      encType: "application/json",
    });
  };

  useEffect(() => {
    if (fetcher.data?.success) {
      setToast({
        message: t("toast.userCreated", { ns: "form" }),
        type: "success",
      });
      reset();
    } else if (fetcher.data?.error) {
      setToast({
        message:
          fetcher.data.error === true
            ? t("internalServerError", { ns: "common" })
            : fetcher.data.error,
        type: "error",
      });
    }
  }, [fetcher.data, t, lang, reset]);

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
    <div className="container">
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("users", { ns: "navigation" })} />
        <form className="form-container" onSubmit={handleSubmit(onSubmit)}>
          <div className="card">
            <div className="card-header">
              <h2 className="card-title">{t("title.createUser", { ns: "form" })}</h2>
              <p className="card-description">{t("description.createUser", { ns: "form" })}</p>
            </div>
            <div className="card-content">
              <div className="form-row">
                <div className="form-group">
                  <label className="label label-required" htmlFor="firstName">
                    {t("firstName", { ns: "form" })}
                  </label>
                  <input
                    type="text"
                    id="firstName"
                    className="input"
                    placeholder={t("firstNamePlaceholder", { ns: "form" })}
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
                  {errors.firstname && (
                    <div className="input-feedback error" id="firstnameError">
                      {errors.firstname.message}
                    </div>
                  )}
                </div>
                <div className="form-group">
                  <label className="label label-required" htmlFor="lastName">
                    {t("lastName", { ns: "form" })}
                  </label>
                  <input
                    type="text"
                    id="lastName"
                    className="input"
                    placeholder={t("lastNamePlaceholder", { ns: "form" })}
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
                  {errors.lastname && (
                    <div className="input-feedback error" id="lastnameError">
                      {errors.lastname.message}
                    </div>
                  )}
                </div>
              </div>

              <div className="form-group">
                <label className="label label-required" htmlFor="email">
                  {t("email", { ns: "form" })}
                </label>
                <input
                  type="email"
                  id="email"
                  className="input"
                  placeholder={t("emailPlaceholder", { ns: "form" })}
                  {...register("email", {
                    required: t("required", { ns: "validation" }),
                    pattern: {
                      value: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,
                      message: t("invalidEmail", { ns: "validation" }),
                    },
                  })}
                />
                {errors.email && (
                  <div className="input-feedback error" id="emailError">
                    {errors.email.message}
                  </div>
                )}
              </div>
              <div className="form-group">
                <label className="label label-required" htmlFor="company">
                  {t("company", { ns: "form" })}
                </label>
                <input
                  type="text"
                  id="company"
                  className="input"
                  placeholder={t("companyPlaceholder", { ns: "form" })}
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
              <div className="form-group">
                <label className="label label-required" htmlFor="password">
                  {t("password", { ns: "form" })}
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
                <label className="label label-required" htmlFor="confirmPassword">
                  {t("passwordConfirmation", { ns: "form" })}
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
              <div className="form-group">
                <label className="label" htmlFor="roles">
                  {t("roles", { ns: "form" })}
                </label>
                <div className="tags-container" id="tagsContainer">
                  {tags.map((tag) => (
                    <div
                      key={tag.value}
                      className={`tag-item ${tag.active ? "" : "inactive"}`}
                      data-value={tag.value}
                      onClick={() => toggleTag(tag.value)}
                    >
                      <svg
                        className="tag-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                      >
                        {tag.active ? (
                          <polyline points="20 6 9 17 4 12"></polyline>
                        ) : (
                          <>
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                          </>
                        )}
                      </svg>
                      {tag.label}
                    </div>
                  ))}
                </div>
                <input
                  type="hidden"
                  id="selectedTags"
                  {...register("roles")}
                  value={tags
                    .filter((tag) => tag.active)
                    .map((tag) => tag.value)
                    .join(",")}
                />
                <p className="helper-text">{t("rolesHelp", { ns: "form" })}</p>
              </div>
              <div className="form-group">
                <label className="label label-required">{t("isVerified", { ns: "form" })}</label>
                <div className="toggle-wrapper">
                  <label className="toggle">
                    <input type="checkbox" id="isVerified" {...register("isVerified")} />
                    <span className="toggle-slider"></span>
                  </label>
                  <label htmlFor="isVerified" className="toggle-label">
                    {t("isVerifiedHelper", { ns: "form" })}
                  </label>
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
              <Link
                to={`/${lang}/dashboard/admin/users`}
                className="button button-outline"
                id="cancelBtn"
              >
                {t("cancel", { ns: "form" })}
              </Link>
            </div>
          </div>
        </form>
      </main>
    </div>
  );
}

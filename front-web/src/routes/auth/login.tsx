import { useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import {
  useFetcher,
  Link,
  useNavigate,
  useSearchParams,
  useParams,
  type LinksFunction,
  type ClientActionFunctionArgs,
  type MetaFunction,
} from "react-router";
import { useForm, type SubmitHandler } from "react-hook-form";
import { api, setAccessToken } from "../../services/auth/auth";
import Toast from "../../components/Toast";
import i18n from "i18next";
import type { LoginFormData } from "../../types/FormType";

export async function clientAction({ request }: ClientActionFunctionArgs) {
  const data = await request.json();
  try {
    const res = await api.post("/api/auth/login", {
      ...data,
      client_type: "web",
    });

    setAccessToken(res.data.token);
    return { success: true };
  } catch (err: any) {
    return { error: err.response.data.message };
  }
}

export const meta: MetaFunction = () => [
  { title: i18n.t("login.metaTitle", { ns: "auth" }) },
  {
    name: "description",
    content: i18n.t("login.metaDescription", { ns: "auth" }),
  },
];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/login.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
];

export default function Login() {
  const { lang } = useParams();
  const [passwordVisible, setPasswordVisible] = useState<boolean>(false);
  const { t } = useTranslation(["auth", "common"]);
  const [searchParams, setSearchParams] = useSearchParams();
  const fetcher = useFetcher();
  const navigate = useNavigate();
  const [toast, setToast] = useState<{
    message: string;
    type: "success" | "error" | "info" | "warning";
  } | null>(null);

  const { register, handleSubmit, reset } = useForm<LoginFormData>();

  const onSubmit: SubmitHandler<LoginFormData> = (data: LoginFormData) => {
    fetcher.submit(data, {
      method: "post",
      encType: "application/json",
    });
  };

  useEffect(() => {
    if (fetcher.data?.success) {
      navigate(`/${lang}/dashboard`);
    } else if (fetcher.data?.error) {
      reset();
      setToast({ message: fetcher.data.error, type: "error" });
    }

    const successParam = searchParams.get("success");
    if (successParam) {
      setToast({
        message: successParam,
        type: "success",
      });
      searchParams.delete("success");
      setSearchParams(searchParams);
    }
  }, [fetcher.data, navigate, searchParams, setSearchParams]);

  const togglePasswordVisibility = (): void => {
    setPasswordVisible((prev) => !prev);
  };

  return (
    <div className="login-container">
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}
      <div className="logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        <span className="logo-text">{t("appName", { ns: "common" })}</span>
      </div>

      <div className="card">
        <div className="card-header">
          <h1 className="card-title">{t("login.title")}</h1>
          <p className="card-description">{t("login.subtitle")}</p>
        </div>

        <form id="loginForm" onSubmit={handleSubmit(onSubmit)}>
          <div className="form-group">
            <label className="label" htmlFor="email">
              {t("login.email")}
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
                {...register("email")}
                type="email"
                className="input input-with-icon"
                placeholder={t("login.emailPlaceholder")}
                required
              />
            </div>
          </div>

          <div className="form-group">
            <label className="label" htmlFor="password">
              {t("login.password")}
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
                {...register("password")}
                type={passwordVisible ? "text" : "password"}
                className="input input-with-icon"
                placeholder="••••••••"
                required
              />
              <svg
                onClick={togglePasswordVisibility}
                className="password-toggle"
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
          </div>

          <div
            style={{
              display: "flex",
              justifyContent: "space-between",
              alignItems: "center",
              marginBottom: "24px",
            }}
          >
            {/* <div className="checkbox-wrapper">
              <input type="checkbox" id="remember" className="checkbox" />
              <label htmlFor="remember" className="checkbox-label">
                {t("login.rememberMe")}
              </label>
            </div> */}
            <Link to={`/${lang}/forgot-password`} className="link">
              {t("login.forgotPassword")}
            </Link>
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
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                <polyline points="10 17 15 12 10 7"></polyline>
                <line x1="15" y1="12" x2="3" y2="12"></line>
              </svg>
            )}
            {fetcher.state === "submitting"
              ? t("loading", { ns: "common" })
              : t("login.loginButton")}
          </button>
        </form>

        <div className="card-footer">
          <p className="footer-text">
            {t("login.noAccount")} &nbsp;
            <Link to={`/${lang}/register`} className="link">
              {t("login.createAccount")}
            </Link>
          </p>
        </div>
      </div>
    </div>
  );
}

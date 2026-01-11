import { useState, useEffect } from "react";
import Toast from "../../components/Toast";
import SideBar from "../../components/SideBar";
import DashboardHeader from "../../components/DashboardHeader";
import { useTranslation } from "react-i18next";
import {
  useFetcher,
  useNavigate,
  useParams,
  type MetaFunction,
  type LinksFunction,
  type ClientActionFunctionArgs,
} from "react-router";
import { useForm, type SubmitHandler } from "react-hook-form";
import i18n from "i18next";
import type { EditUserFormData } from "../../types/FormType";
import type { ApiErrorResponse } from "../../types/ApiType";
import { api, setAccessToken } from "../../services/auth/auth";
import ChangePassword from "../../components/ChangePassword";

export async function clientAction({ request }: ClientActionFunctionArgs) {
  const data = await request.json();

  if (!data.id) {
    return { error: true };
  }

  try {
    await api.put(`/api/users/${data.id}`, data);
    return { success: true };
  } catch (err: any) {
    const apiError = err.response?.data as ApiErrorResponse;
    if (apiError?.details) {
      const firstError = Object.values(apiError.details)[0];
      return { error: firstError?.[0] || true };
    }
    return { error: true };
  }
}

export async function clientLoader(): Promise<EditUserFormData | { error: string }> {
  try {
    const response = await api.get("/api/auth/me");
    return response.data;
  } catch (err: any) {
    const apiError = err.response?.data as ApiErrorResponse;

    return { error: apiError.message };
  }
}

export const meta: MetaFunction = () => [
  { title: i18n.t("metaTitle", { title: i18n.t("settings", { ns: "navigation" }), ns: "common" }) },
];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/ui/dashboard-header.css" },
  { rel: "stylesheet", href: "/assets/css/ui/sidebar.css" },
  { rel: "stylesheet", href: "/assets/css/ui/button.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
  { rel: "stylesheet", href: "/assets/css/ui/form.css" },
];

export default function Settings({
  loaderData,
}: {
  loaderData: EditUserFormData | { error: string };
}) {
  const { t } = useTranslation(["settings", "validation", "navigation", "common"]);
  const navigate = useNavigate();
  const { lang } = useParams();
  const fetcher = useFetcher();
  const [toast, setToast] = useState<{
    message: string;
    type: "success" | "error" | "info" | "warning";
  } | null>(null);

  const {
    register,
    handleSubmit,
    formState: { errors },
  } = useForm<EditUserFormData>({
    defaultValues: "error" in loaderData ? undefined : loaderData,
  });

  const onSubmit: SubmitHandler<EditUserFormData> = (data: EditUserFormData) => {
    const dataWithId = {
      ...data,
      id: "id" in loaderData ? loaderData.id : null,
    };

    fetcher.submit(dataWithId, {
      method: "post",
      encType: "application/json",
    });
  };

  useEffect(() => {
    if (fetcher.data?.success) {
      setAccessToken(null);
      navigate(`/${lang}`);
    } else if (fetcher.data?.error) {
      setToast({
        message:
          fetcher.data.error == true
            ? t("internalServerError", { ns: "common" })
            : fetcher.data.error,
        type: "error",
      });
    }
  }, [fetcher.data, navigate, lang]);

  useEffect(() => {
    if ("error" in loaderData) {
      setToast({
        message: loaderData.error,
        type: "error",
      });
    }
  }, [loaderData]);

  return (
    <div className="container">
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("settings", { ns: "navigation" })} />
        <form className="form-container" onSubmit={handleSubmit(onSubmit)}>
          <div className="card">
            <div className="card-header">
              <h2 className="card-title">{t("profile.title", { ns: "settings" })}</h2>
              <p className="card-description">{t("profile.description", { ns: "settings" })}</p>
            </div>
            <div className="card-content">
              <div className="form-row">
                <div className="form-group">
                  <label className="label label-required" htmlFor="firstName">
                    {t("profile.firstName", { ns: "settings" })}
                  </label>
                  <input
                    type="text"
                    id="firstName"
                    className="input"
                    placeholder={t("profile.firstNamePlaceholder", { ns: "settings" })}
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
                    {t("profile.lastName", { ns: "settings" })}
                  </label>
                  <input
                    type="text"
                    id="lastName"
                    className="input"
                    placeholder={t("profile.lastNamePlaceholder", { ns: "settings" })}
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
                  {t("profile.email", { ns: "settings" })}
                </label>
                <input
                  type="email"
                  id="email"
                  className="input"
                  placeholder={t("profile.emailPlaceholder", { ns: "settings" })}
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
                  {t("profile.company", { ns: "settings" })}
                </label>
                <input
                  type="text"
                  id="company"
                  className="input"
                  placeholder={t("profile.companyPlaceholder", { ns: "settings" })}
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
                {t("save", { ns: "settings" })}
              </button>
            </div>
          </div>
        </form>
        <ChangePassword userId={"id" in loaderData ? loaderData.id : null} />
      </main>
    </div>
  );
}

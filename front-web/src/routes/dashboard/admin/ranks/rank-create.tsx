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
import type { ApiErrorResponse } from "../../../../types/ApiType";
import { api } from "../../../../services/auth";
import type { AxiosError } from "axios";
import type { CreateRankFormData } from "../../../../types/FormType";

export async function clientAction({ request }: ClientActionFunctionArgs) {
  const data = await request.json();

  try {
    await api.post(`/api/ranks`, data);
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
  { title: i18n.t("metaTitle", { title: i18n.t("ranks", { ns: "navigation" }), ns: "common" }) },
];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/ui/dashboard-header.css" },
  { rel: "stylesheet", href: "/assets/css/ui/sidebar.css" },
  { rel: "stylesheet", href: "/assets/css/ui/button.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
  { rel: "stylesheet", href: "/assets/css/ui/form.css" },
];

export default function RankCreate() {
  const { t } = useTranslation(["form", "validation", "navigation", "common"]);
  const { lang } = useParams();
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
  } = useForm<CreateRankFormData>();

  const onSubmit: SubmitHandler<CreateRankFormData> = (data: CreateRankFormData) => {
    fetcher.submit(data as never, {
      method: "post",
      encType: "application/json",
    });
  };

  useEffect(() => {
    if (fetcher.data?.success) {
      setToast({
        message: t("toast.rankCreated", { ns: "form" }),
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

  return (
    <div className="container">
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("ranks", { ns: "navigation" })} />
        <form className="form-container" onSubmit={handleSubmit(onSubmit)}>
          <div className="card">
            <div className="card-header">
              <h2 className="card-title">{t("title.createRank", { ns: "form" })}</h2>
              <p className="card-description">{t("description.createRank", { ns: "form" })}</p>
            </div>
            <div className="card-content">
              <div className="form-group">
                <label className="label label-required" htmlFor="level">
                  {t("level", { ns: "form" })}
                </label>
                <input
                  type="number"
                  id="level"
                  className="input"
                  placeholder={t("levelPlaceholder", { ns: "form" })}
                  {...register("level", {
                    required: t("required", { ns: "validation" }),
                    valueAsNumber: true,
                  })}
                />
                {errors.level && (
                  <div className="input-feedback error" id="levelError">
                    {errors.level.message}
                  </div>
                )}
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label className="label label-required" htmlFor="experienceMin">
                    {t("experienceMin", { ns: "form" })}
                  </label>
                  <input
                    type="number"
                    id="experienceMin"
                    className="input"
                    placeholder={t("experienceMinPlaceholder", { ns: "form" })}
                    {...register("experienceMin", {
                      required: t("required", { ns: "validation" }),
                      valueAsNumber: true,
                    })}
                  />
                  {errors.experienceMin && (
                    <div className="input-feedback error" id="experienceMinError">
                      {errors.experienceMin.message}
                    </div>
                  )}
                </div>

                <div className="form-group">
                  <label className="label label-required" htmlFor="experienceMax">
                    {t("experienceMax", { ns: "form" })}
                  </label>
                  <input
                    type="number"
                    id="experienceMax"
                    className="input"
                    placeholder={t("experienceMaxPlaceholder", { ns: "form" })}
                    {...register("experienceMax", {
                      required: t("required", { ns: "validation" }),
                      valueAsNumber: true,
                    })}
                  />
                  {errors.experienceMax && (
                    <div className="input-feedback error" id="experienceMaxError">
                      {errors.experienceMax.message}
                    </div>
                  )}
                </div>
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label className="label label-required" htmlFor="translationFr">
                    {t("translationFr", { ns: "form" })}
                  </label>
                  <input
                    type="text"
                    id="translationFr"
                    className="input"
                    placeholder={t("translationFrPlaceholder", { ns: "form" })}
                    {...register("translations.fr", {
                      required: t("required", { ns: "validation" }),
                      maxLength: {
                        value: 255,
                        message: t("maxLength", { max: 255, ns: "validation" }),
                      },
                    })}
                  />
                  {errors.translations?.fr && (
                    <div className="input-feedback error" id="translationFrError">
                      {errors.translations.fr.message}
                    </div>
                  )}
                </div>

                <div className="form-group">
                  <label className="label label-required" htmlFor="translationEn">
                    {t("translationEn", { ns: "form" })}
                  </label>
                  <input
                    type="text"
                    id="translationEn"
                    className="input"
                    placeholder={t("translationEnPlaceholder", { ns: "form" })}
                    {...register("translations.en", {
                      required: t("required", { ns: "validation" }),
                      maxLength: {
                        value: 255,
                        message: t("maxLength", { max: 255, ns: "validation" }),
                      },
                    })}
                  />
                  {errors.translations?.en && (
                    <div className="input-feedback error" id="translationEnError">
                      {errors.translations.en.message}
                    </div>
                  )}
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
                to={`/${lang}/dashboard/admin/ranks`}
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

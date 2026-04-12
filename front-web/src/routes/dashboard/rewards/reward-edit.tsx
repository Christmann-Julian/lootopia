import { useState, useEffect } from "react";
import Toast from "../../../components/Toast";
import SideBar from "../../../components/SideBar";
import DashboardHeader from "../../../components/DashboardHeader";
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
import type { ApiErrorResponse } from "../../../types/ApiType";
import { api } from "../../../services/auth";
import type { AxiosError } from "axios";
import type { EditRewardFormData } from "../../../types/FormType";

export async function clientAction({ request }: ClientActionFunctionArgs) {
  const data = await request.json();

  if (!data.id) {
    return { error: true };
  }

  try {
    await api.put(`/api/rewards/${data.id}`, data);
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

export async function clientLoader({
  params,
}: {
  params: { id: string };
}): Promise<EditRewardFormData | { error: string }> {
  try {
    const { id } = params;
    const response = await api.get(`/api/rewards/${id}`, {
      headers: {
        "X-Skip-Locale": "true",
      },
    });

    const data = response.data;

    // L'API renvoie la date au format ATOM (ex: "2026-12-31T23:59:00+00:00")
    // L'input type="datetime-local" requiert le format "YYYY-MM-DDTHH:mm"
    if (data.endDate) {
      data.endDate = data.endDate.substring(0, 16);
    }

    return data;
  } catch (err: unknown) {
    const axiosError = err as AxiosError<ApiErrorResponse>;
    const apiError = axiosError.response?.data;

    return { error: apiError?.message || "An unexpected error occurred" };
  }
}

export const meta: MetaFunction = () => [
  { title: i18n.t("metaTitle", { title: i18n.t("rewards", { ns: "navigation" }), ns: "common" }) },
];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/ui/dashboard-header.css" },
  { rel: "stylesheet", href: "/assets/css/ui/sidebar.css" },
  { rel: "stylesheet", href: "/assets/css/ui/button.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
  { rel: "stylesheet", href: "/assets/css/ui/form.css" },
];

export default function RewardEdit({
  loaderData,
}: {
  loaderData: EditRewardFormData | { error: string };
}) {
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
    formState: { errors },
  } = useForm<EditRewardFormData>({
    defaultValues: "error" in loaderData ? undefined : loaderData,
  });

  const onSubmit: SubmitHandler<EditRewardFormData> = (data: EditRewardFormData) => {
    const dataWithId = {
      ...data,
      id: "id" in loaderData ? loaderData.id : null,
    };

    const cleanedData = JSON.parse(JSON.stringify(dataWithId));

    fetcher.submit(cleanedData, {
      method: "post",
      encType: "application/json",
    });
  };

  useEffect(() => {
    if (fetcher.data?.success) {
      setToast({
        message: t("toast.rewardUpdated", { ns: "form" }),
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
  }, [fetcher.data, t, lang]);

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
        <DashboardHeader title={t("rewards", { ns: "navigation" })} />
        <form className="form-container" onSubmit={handleSubmit(onSubmit)}>
          <div className="card">
            <div className="card-header">
              <h2 className="card-title">{t("title.editReward", { ns: "form" })}</h2>
              <p className="card-description">{t("description.editReward", { ns: "form" })}</p>
            </div>
            <div className="card-content">
              <div className="form-row">
                <div className="form-group">
                  <label className="label label-required" htmlFor="code">
                    {t("code", { ns: "form" })}
                  </label>
                  <input
                    type="text"
                    id="code"
                    className="input"
                    placeholder={t("codePlaceholder", { ns: "form" })}
                    {...register("code", {
                      required: t("required", { ns: "validation" }),
                      maxLength: {
                        value: 255,
                        message: t("maxLength", { max: 255, ns: "validation" }),
                      },
                    })}
                  />
                  {errors.code && (
                    <div className="input-feedback error" id="codeError">
                      {errors.code.message}
                    </div>
                  )}
                </div>

                <div className="form-group">
                  <label className="label label-required" htmlFor="endDate">
                    {t("endDate", { ns: "form" })}
                  </label>
                  <input
                    type="datetime-local"
                    id="endDate"
                    className="input"
                    {...register("endDate", {
                      required: t("required", { ns: "validation" }),
                    })}
                  />
                  {errors.endDate && (
                    <div className="input-feedback error" id="endDateError">
                      {errors.endDate.message}
                    </div>
                  )}
                </div>
              </div>

              <div className="form-group">
                <label className="label label-required" htmlFor="link">
                  {t("link", { ns: "form" })}
                </label>
                <input
                  type="url"
                  id="link"
                  className="input"
                  placeholder={t("linkPlaceholder", { ns: "form" })}
                  {...register("link", {
                    required: t("required", { ns: "validation" }),
                    pattern: {
                      value: /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,6})([\w.-]*)*\/?$/,
                      message: t("invalidUrl", { ns: "validation" }),
                    },
                    maxLength: {
                      value: 255,
                      message: t("maxLength", { max: 255, ns: "validation" }),
                    },
                  })}
                />
                {errors.link && (
                  <div className="input-feedback error" id="linkError">
                    {errors.link.message}
                  </div>
                )}
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label className="label label-required" htmlFor="translationFr">
                    {t("titleFr", { ns: "form" })}
                  </label>
                  <input
                    type="text"
                    id="translationFr"
                    className="input"
                    placeholder={t("titleFrPlaceholder", { ns: "form" })}
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
                    {t("titleEn", { ns: "form" })}
                  </label>
                  <input
                    type="text"
                    id="translationEn"
                    className="input"
                    placeholder={t("titleEnPlaceholder", { ns: "form" })}
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
                to={`/${lang}/dashboard/rewards`}
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

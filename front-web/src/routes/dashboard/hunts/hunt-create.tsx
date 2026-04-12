import { useState, useEffect } from "react";
import Toast from "../../../components/Toast";
import SideBar from "../../../components/SideBar";
import DashboardHeader from "../../../components/DashboardHeader";
import { useTranslation } from "react-i18next";
import {
  useFetcher,
  useParams,
  useLoaderData,
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
import type { CreateHuntFormData } from "../../../types/FormType";

export async function clientAction({ request }: ClientActionFunctionArgs) {
  const data = await request.json();

  try {
    await api.post(`/api/hunts`, data);
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

export async function clientLoader() {
  try {
    const [categoriesRes, raritiesRes] = await Promise.all([
      api.get("/api/categories", { headers: { "X-Skip-Locale": "true" } }),
      api.get("/api/rarities", { headers: { "X-Skip-Locale": "true" } }),
    ]);

    return {
      categories: categoriesRes.data.data || [],
      rarities: raritiesRes.data.data || [],
    };
  } catch {
    return { error: "Failed to load dependencies" };
  }
}

export const meta: MetaFunction = () => [
  { title: i18n.t("metaTitle", { title: i18n.t("hunts", { ns: "navigation" }), ns: "common" }) },
];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/ui/dashboard-header.css" },
  { rel: "stylesheet", href: "/assets/css/ui/sidebar.css" },
  { rel: "stylesheet", href: "/assets/css/ui/button.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
  { rel: "stylesheet", href: "/assets/css/ui/form.css" },
];

export default function HuntCreate() {
  const { t } = useTranslation(["form", "validation", "navigation", "common"]);
  const { lang } = useParams();
  const fetcher = useFetcher();
  const loaderData = useLoaderData();
  const [toast, setToast] = useState<{
    message: string;
    type: "success" | "error" | "info" | "warning";
  } | null>(null);

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<CreateHuntFormData>();

  const onSubmit: SubmitHandler<CreateHuntFormData> = (data: CreateHuntFormData) => {
    fetcher.submit(data as never, {
      method: "post",
      encType: "application/json",
    });
  };

  useEffect(() => {
    if (fetcher.data?.success) {
      setToast({
        message: t("toast.huntCreated", { ns: "form" }),
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

  if (loaderData?.error) {
    return (
      <div className="container">
        <p className="error">{loaderData.error}</p>
      </div>
    );
  }

  return (
    <div className="container">
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("hunts", { ns: "navigation" })} />
        <form className="form-container" onSubmit={handleSubmit(onSubmit)}>
          <div className="card">
            <div className="card-header">
              <h2 className="card-title">{t("title.createHunt", { ns: "form" })}</h2>
              <p className="card-description">{t("description.createHunt", { ns: "form" })}</p>
            </div>
            <div className="card-content">
              <h3 className="section-title">{t("generalHunt", { ns: "form" })}</h3>
              <div className="form-row">
                <div className="form-group">
                  <label className="label label-required" htmlFor="lat">
                    {t("latitude", { ns: "form" })}
                  </label>
                  <input
                    type="number"
                    step="any"
                    id="lat"
                    className="input"
                    {...register("lat", { required: true, valueAsNumber: true })}
                  />
                  {errors.lat && (
                    <div className="input-feedback error">
                      {t("required", { ns: "validation" })}
                    </div>
                  )}
                </div>
                <div className="form-group">
                  <label className="label label-required" htmlFor="lon">
                    {t("longitude", { ns: "form" })}
                  </label>
                  <input
                    type="number"
                    step="any"
                    id="lon"
                    className="input"
                    {...register("lon", { required: true, valueAsNumber: true })}
                  />
                  {errors.lon && (
                    <div className="input-feedback error">
                      {t("required", { ns: "validation" })}
                    </div>
                  )}
                </div>
              </div>

              <div className="form-row">
                <div className="form-group">
                  <label className="label" htmlFor="categoryId">
                    {t("category", { ns: "form" })}
                  </label>
                  <select
                    id="categoryId"
                    className="input"
                    {...register("categoryId", { valueAsNumber: true })}
                  >
                    <option value="">{t("selectOption", { ns: "form" })}</option>
                    {loaderData.categories.map(
                      (cat: { id: number; translations?: { fr: string } }) => (
                        <option key={cat.id} value={cat.id}>
                          {cat.translations?.fr || cat.id}
                        </option>
                      )
                    )}
                  </select>
                </div>
                <div className="form-group">
                  <label className="label label-required" htmlFor="rarityId">
                    {t("rarity", { ns: "form" })}
                  </label>
                  <select
                    id="rarityId"
                    className="input"
                    {...register("rarityId", { required: true, valueAsNumber: true })}
                  >
                    <option value="">{t("selectOption", { ns: "form" })}</option>
                    {loaderData.rarities.map(
                      (rarity: { id: number; translations?: { fr: string } }) => (
                        <option key={rarity.id} value={rarity.id}>
                          {rarity.translations?.fr || rarity.id}
                        </option>
                      )
                    )}
                  </select>
                  {errors.rarityId && (
                    <div className="input-feedback error">
                      {t("required", { ns: "validation" })}
                    </div>
                  )}
                </div>
              </div>

              <hr className="divider" />

              <h3 className="section-title">{t("translationFr", { ns: "form" })}</h3>
              <div className="form-group">
                <label className="label label-required">{t("titleField", { ns: "form" })}</label>
                <input
                  type="text"
                  className="input"
                  {...register("translations.fr.title", { required: true })}
                />
              </div>
              <div className="form-group">
                <label className="label label-required">
                  {t("descriptionField", { ns: "form" })}
                </label>
                <textarea
                  className="input"
                  {...register("translations.fr.description", { required: true })}
                />
              </div>
              <div className="form-row">
                <div className="form-group">
                  <label className="label label-required">{t("question", { ns: "form" })}</label>
                  <input
                    type="text"
                    className="input"
                    {...register("translations.fr.question", { required: true })}
                  />
                </div>
                <div className="form-group">
                  <label className="label label-required">{t("answer", { ns: "form" })}</label>
                  <input
                    type="text"
                    className="input"
                    {...register("translations.fr.answer", { required: true })}
                  />
                </div>
              </div>
              <div className="form-group">
                <label className="label label-required">{t("location", { ns: "form" })}</label>
                <input
                  type="text"
                  className="input"
                  {...register("translations.fr.location", { required: true })}
                />
              </div>

              <hr className="divider" />

              <h3 className="section-title">{t("translationEn", { ns: "form" })}</h3>
              <div className="form-group">
                <label className="label label-required">{t("titleField", { ns: "form" })}</label>
                <input
                  type="text"
                  className="input"
                  {...register("translations.en.title", { required: true })}
                />
              </div>
              <div className="form-group">
                <label className="label label-required">
                  {t("descriptionField", { ns: "form" })}
                </label>
                <textarea
                  className="input"
                  {...register("translations.en.description", { required: true })}
                />
              </div>
              <div className="form-row">
                <div className="form-group">
                  <label className="label label-required">{t("question", { ns: "form" })}</label>
                  <input
                    type="text"
                    className="input"
                    {...register("translations.en.question", { required: true })}
                  />
                </div>
                <div className="form-group">
                  <label className="label label-required">{t("answer", { ns: "form" })}</label>
                  <input
                    type="text"
                    className="input"
                    {...register("translations.en.answer", { required: true })}
                  />
                </div>
              </div>
              <div className="form-group">
                <label className="label label-required">{t("location", { ns: "form" })}</label>
                <input
                  type="text"
                  className="input"
                  {...register("translations.en.location", { required: true })}
                />
              </div>

              <hr className="divider" />

              <h3 className="section-title">{t("associatedReward", { ns: "form" })}</h3>
              <div className="form-row">
                <div className="form-group">
                  <label className="label label-required">{t("code", { ns: "form" })}</label>
                  <input
                    type="text"
                    className="input"
                    {...register("reward.code", { required: true })}
                  />
                </div>
                <div className="form-group">
                  <label className="label label-required">{t("endDate", { ns: "form" })}</label>
                  <input
                    type="datetime-local"
                    className="input"
                    {...register("reward.endDate", { required: true })}
                  />
                </div>
              </div>
              <div className="form-group">
                <label className="label label-required">{t("link", { ns: "form" })}</label>
                <input
                  type="url"
                  className="input"
                  {...register("reward.link", { required: true })}
                />
              </div>
              <div className="form-row">
                <div className="form-group">
                  <label className="label label-required">
                    {t("rewardTitleFr", { ns: "form" })}
                  </label>
                  <input
                    type="text"
                    className="input"
                    {...register("reward.translations.fr", { required: true })}
                  />
                </div>
                <div className="form-group">
                  <label className="label label-required">
                    {t("rewardTitleEn", { ns: "form" })}
                  </label>
                  <input
                    type="text"
                    className="input"
                    {...register("reward.translations.en", { required: true })}
                  />
                </div>
              </div>
            </div>
            <div className="form-actions">
              <button
                type="submit"
                className="button button-primary"
                disabled={fetcher.state === "submitting"}
              >
                {t("save", { ns: "form" })}
              </button>
              <Link to={`/${lang}/dashboard/hunts`} className="button button-outline">
                {t("cancel", { ns: "form" })}
              </Link>
            </div>
          </div>
        </form>
      </main>
    </div>
  );
}

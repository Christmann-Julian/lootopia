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
import type { EditHuntFormData } from "../../../types/FormType";

export async function clientAction({ request }: ClientActionFunctionArgs) {
  const data = await request.json();

  if (!data.id) return { error: true };

  try {
    await api.put(`/api/hunts/${data.id}`, data);
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

export async function clientLoader({ params }: { params: { id: string } }) {
  try {
    const { id } = params;
    const [huntRes, categoriesRes, raritiesRes] = await Promise.all([
      api.get(`/api/hunts/${id}`, { headers: { "X-Skip-Locale": "true" } }),
      api.get("/api/categories", { headers: { "X-Skip-Locale": "true" } }),
      api.get("/api/rarities", { headers: { "X-Skip-Locale": "true" } }),
    ]);

    const huntData = huntRes.data;

    huntData.categoryId = huntData.category?.id || null;
    huntData.rarityId = huntData.rarity?.id || null;

    return {
      hunt: huntData,
      categories: categoriesRes.data.data || [],
      rarities: raritiesRes.data.data || [],
    };
  } catch (err: unknown) {
    const axiosError = err as AxiosError<ApiErrorResponse>;
    return { error: axiosError.response?.data?.message || "An unexpected error occurred" };
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

export default function HuntEdit() {
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
    formState: { errors },
  } = useForm<EditHuntFormData>({
    defaultValues: loaderData?.error ? undefined : loaderData.hunt,
  });

  const onSubmit: SubmitHandler<EditHuntFormData> = (data: EditHuntFormData) => {
    const dataWithId = {
      ...data,
      id: loaderData?.hunt?.id || null,
    };

    fetcher.submit(dataWithId, {
      method: "post",
      encType: "application/json",
    });
  };

  useEffect(() => {
    if (fetcher.data?.success) {
      setToast({
        message: t("toast.huntUpdated", { ns: "form" }),
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
    if (loaderData?.error) {
      setToast({
        message: loaderData.error,
        type: "error",
      });
    }
  }, [loaderData]);

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
              <h2 className="card-title">{t("title.editHunt", { ns: "form" })}</h2>
              <p className="card-description">{t("description.editHunt", { ns: "form" })}</p>
            </div>
            <div className="card-content">
              <h3 className="section-title">Paramètres de la chasse</h3>
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

import SideBar from "../../../components/SideBar";
import DashboardHeader from "../../../components/DashboardHeader";
import { useTranslation } from "react-i18next";
import { type MetaFunction, type LinksFunction, useParams } from "react-router";
import i18n from "i18next";
import type { ApiErrorResponse } from "../../../types/ApiType";
import { api } from "../../../services/auth";
import type { AxiosError } from "axios";
import Show from "../../../components/Show";
import type { HuntShowData } from "../../../types/ShowType";

export async function clientLoader({
  params,
}: {
  params: { id: string };
}): Promise<HuntShowData | { error: string }> {
  try {
    const { id } = params;
    const response = await api.get(`/api/hunts/${id}`);
    return response.data;
  } catch (err: unknown) {
    const axiosError = err as AxiosError<ApiErrorResponse>;
    const apiError = axiosError.response?.data;

    return { error: apiError?.message || "An unexpected error occurred" };
  }
}

export const meta: MetaFunction = () => [
  { title: i18n.t("metaTitle", { title: i18n.t("hunts", { ns: "navigation" }), ns: "common" }) },
];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/ui/dashboard-header.css" },
  { rel: "stylesheet", href: "/assets/css/ui/sidebar.css" },
  { rel: "stylesheet", href: "/assets/css/ui/show.css" },
  { rel: "stylesheet", href: "/assets/css/ui/button.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
];

export default function HuntShow({ loaderData }: { loaderData: HuntShowData | { error: string } }) {
  const { t } = useTranslation(["show", "navigation", "common"]);
  const { lang } = useParams();
  const data =
    "lat" in loaderData
      ? {
          ...loaderData,
          company: loaderData.company || t("empty", { ns: "show" }),
          category: loaderData.category?.name || t("empty", { ns: "show" }),
          rarity: loaderData.rarity?.name || t("empty", { ns: "show" }),
          reward: loaderData.reward?.code || t("empty", { ns: "show" }),
          title: loaderData.title || t("empty", { ns: "show" }),
          description: loaderData.description || t("empty", { ns: "show" }),
          question: loaderData.question || t("empty", { ns: "show" }),
          answer: loaderData.answer || t("empty", { ns: "show" }),
          location: loaderData.location || t("empty", { ns: "show" }),
        }
      : loaderData;

  return (
    <div className="container">
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("hunts", { ns: "navigation" })} />
        <Show
          title={t("title.huntDetails", { ns: "show", defaultValue: "Hunt Details" })}
          data={data}
          backUrl={`/${lang}/dashboard/hunts`}
          editUrl={"id" in data ? `/${lang}/dashboard/hunts/${data.id}/edit` : undefined}
        />
      </main>
    </div>
  );
}

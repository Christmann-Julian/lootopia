import SideBar from "../../../../components/SideBar";
import DashboardHeader from "../../../../components/DashboardHeader";
import { useTranslation } from "react-i18next";
import { type MetaFunction, type LinksFunction, useParams } from "react-router";
import i18n from "i18next";
import type { ApiErrorResponse } from "../../../../types/ApiType";
import { api } from "../../../../services/auth";
import type { AxiosError } from "axios";
import Show from "../../../../components/Show";
import type { RankShowData } from "../../../../types/ShowType";

export async function clientLoader({
  params,
}: {
  params: { id: string };
}): Promise<RankShowData | { error: string }> {
  try {
    const { id } = params;
    const response = await api.get(`/api/ranks/${id}`);
    return response.data;
  } catch (err: unknown) {
    const axiosError = err as AxiosError<ApiErrorResponse>;
    const apiError = axiosError.response?.data;

    return { error: apiError?.message || "An unexpected error occurred" };
  }
}

export const meta: MetaFunction = () => [
  { title: i18n.t("metaTitle", { title: i18n.t("ranks", { ns: "navigation" }), ns: "common" }) },
];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/ui/dashboard-header.css" },
  { rel: "stylesheet", href: "/assets/css/ui/sidebar.css" },
  { rel: "stylesheet", href: "/assets/css/ui/show.css" },
  { rel: "stylesheet", href: "/assets/css/ui/button.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
];

export default function RankShow({ loaderData }: { loaderData: RankShowData | { error: string } }) {
  const { t } = useTranslation(["show", "navigation", "common"]);
  const { lang } = useParams();

  const data = "level" in loaderData ? { ...loaderData } : loaderData;

  return (
    <div className="container">
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("ranks", { ns: "navigation" })} />
        <Show
          title={t("titleDetails.rank", { ns: "show", defaultValue: "Rank Details" })}
          data={data}
          backUrl={`/${lang}/dashboard/admin/ranks`}
          editUrl={"id" in data ? `/${lang}/dashboard/admin/ranks/${data.id}/edit` : undefined}
        />
      </main>
    </div>
  );
}

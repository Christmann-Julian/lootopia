import SideBar from "../../../../components/SideBar";
import DashboardHeader from "../../../../components/DashboardHeader";
import { useTranslation } from "react-i18next";
import { type MetaFunction, type LinksFunction, useParams } from "react-router";
import i18n from "i18next";
import type { ApiErrorResponse } from "../../../../types/ApiType";
import { api } from "../../../../services/auth";
import type { AxiosError } from "axios";
import Show from "../../../../components/Show";
import { type CategoryShowData } from "../../../../types/ShowType";

export async function clientLoader({
  params,
}: {
  params: { id: string };
}): Promise<CategoryShowData | { error: string }> {
  try {
    const { id } = params;
    const response = await api.get(`/api/categories/${id}`);
    return response.data;
  } catch (err: unknown) {
    const axiosError = err as AxiosError<ApiErrorResponse>;
    const apiError = axiosError.response?.data;

    return { error: apiError?.message || "An unexpected error occurred" };
  }
}

export const meta: MetaFunction = () => [
  {
    title: i18n.t("metaTitle", { title: i18n.t("categories", { ns: "navigation" }), ns: "common" }),
  },
];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/ui/dashboard-header.css" },
  { rel: "stylesheet", href: "/assets/css/ui/sidebar.css" },
  { rel: "stylesheet", href: "/assets/css/ui/show.css" },
  { rel: "stylesheet", href: "/assets/css/ui/button.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
];

export default function CategoryShow({
  loaderData,
}: {
  loaderData: CategoryShowData | { error: string };
}) {
  const { t } = useTranslation(["show", "navigation", "common"]);
  const { lang } = useParams();

  const data = "icon" in loaderData ? { ...loaderData } : loaderData;

  return (
    <div className="container">
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("categories", { ns: "navigation" })} />
        <Show
          title={t("titleDetails.category", { ns: "show", defaultValue: "Category Details" })}
          data={data}
          backUrl={`/${lang}/dashboard/admin/categories`}
          editUrl={"id" in data ? `/${lang}/dashboard/admin/categories/${data.id}/edit` : undefined}
        />
      </main>
    </div>
  );
}

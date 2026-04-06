import SideBar from "../../../../components/SideBar";
import DashboardHeader from "../../../../components/DashboardHeader";
import { useTranslation } from "react-i18next";
import { type MetaFunction, type LinksFunction, useParams } from "react-router";
import i18n from "i18next";
import type { UserShowData } from "../../../../types/ShowType";
import type { ApiErrorResponse } from "../../../../types/ApiType";
import { api } from "../../../../services/auth";
import type { AxiosError } from "axios";
import Show from "../../../../components/Show";

export async function clientLoader({
  params,
}: {
  params: { id: string };
}): Promise<UserShowData | { error: string }> {
  try {
    const { id } = params;
    const response = await api.get(`/api/users/${id}`);
    return response.data;
  } catch (err: unknown) {
    const axiosError = err as AxiosError<ApiErrorResponse>;
    const apiError = axiosError.response?.data;

    return { error: apiError?.message || "An unexpected error occurred" };
  }
}

export const meta: MetaFunction = () => [
  { title: i18n.t("metaTitle", { title: i18n.t("users", { ns: "navigation" }), ns: "common" }) },
];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/ui/dashboard-header.css" },
  { rel: "stylesheet", href: "/assets/css/ui/sidebar.css" },
  { rel: "stylesheet", href: "/assets/css/ui/show.css" },
  { rel: "stylesheet", href: "/assets/css/ui/button.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
];

export default function UserShow({ loaderData }: { loaderData: UserShowData | { error: string } }) {
  const { t } = useTranslation(["show", "navigation", "common"]);
  const { lang } = useParams();
  const data =
    "roles" in loaderData
      ? {
          ...loaderData,
          roles: loaderData.roles.join(", "),
          rank: loaderData.rank
            ? `${loaderData.rank.level} (Exp: ${loaderData.rank.experienceMin}-${loaderData.rank.experienceMax})`
            : t("empty", { ns: "show" }),
          badges: loaderData.badges
            ? loaderData.badges
                .map((badge) => badge.translations?.[lang || "en"] || badge.icon)
                .join(", ")
            : t("empty", { ns: "show" }),
        }
      : loaderData;

  return (
    <div className="container">
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("users", { ns: "navigation" })} />
        <Show
          title={t("titleDetails.user", { ns: "show" })}
          data={data}
          backUrl={`/${lang}/dashboard/admin/users`}
          editUrl={"id" in data ? `/${lang}/dashboard/admin/users/${data.id}/edit` : undefined}
        />
      </main>
    </div>
  );
}

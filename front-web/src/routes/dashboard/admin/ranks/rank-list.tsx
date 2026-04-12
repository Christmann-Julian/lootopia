import SideBar from "../../../../components/SideBar";
import DashboardHeader from "../../../../components/DashboardHeader";
import { useTranslation } from "react-i18next";
import type { LinksFunction, MetaFunction } from "react-router";
import i18n from "i18next";
import Table from "../../../../components/Table";
import type { Column } from "../../../../types/TableType";

export const meta: MetaFunction = () => [
  { title: i18n.t("metaTitle", { title: i18n.t("ranks", { ns: "navigation" }), ns: "common" }) },
];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/ui/dashboard-header.css" },
  { rel: "stylesheet", href: "/assets/css/ui/sidebar.css" },
  { rel: "stylesheet", href: "/assets/css/ui/button.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
  { rel: "stylesheet", href: "/assets/css/ui/table.css" },
  { rel: "stylesheet", href: "/assets/css/ui/pagination.css" },
  { rel: "stylesheet", href: "/assets/css/ui/popup.css" },
];

export default function RankList() {
  const { t } = useTranslation(["navigation", "list-view", "common"]);

  const columns: Column<Record<string, unknown>>[] = [
    { key: "id", label: t("columns.id", { ns: "list-view" }), sortable: true },
    { key: "name", label: t("columns.name", { ns: "list-view" }), sortable: false },
    { key: "level", label: t("columns.level", { ns: "list-view" }), sortable: true },
    {
      key: "experienceMin",
      label: t("columns.experienceMin", { ns: "list-view" }),
      sortable: true,
    },
    {
      key: "experienceMax",
      label: t("columns.experienceMax", { ns: "list-view" }),
      sortable: true,
    },
  ];

  return (
    <div className="container">
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("ranks", { ns: "navigation" })} />
        <Table
          title={t("title.ranks", { ns: "list-view" })}
          columns={columns}
          apiEndpoint="/api/ranks"
        />
      </main>
    </div>
  );
}

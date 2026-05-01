import SideBar from "../../../components/SideBar";
import DashboardHeader from "../../../components/DashboardHeader";
import { useTranslation } from "react-i18next";
import type { LinksFunction, MetaFunction } from "react-router";
import i18n from "i18next";
import Table from "../../../components/Table";
import type { Column } from "../../../types/TableType";

export const meta: MetaFunction = () => [
  { title: i18n.t("metaTitle", { title: i18n.t("hunts", { ns: "navigation" }), ns: "common" }) },
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

export default function HuntList() {
  const { t } = useTranslation(["navigation", "list-view", "common"]);

  const columns: Column<Record<string, unknown>>[] = [
    { key: "id", label: t("columns.id", { ns: "list-view" }), sortable: true },
    {
      key: "title",
      label: t("columns.title", { ns: "list-view" }),
      sortable: false,
    },
    {
      key: "location",
      label: t("columns.location", { ns: "list-view" }),
      sortable: false,
    },
  ];

  return (
    <div className="container">
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("hunts", { ns: "navigation" })} />
        <Table
          title={t("title.hunts", { ns: "list-view" })}
          columns={columns}
          apiEndpoint="/api/hunts"
        />
      </main>
    </div>
  );
}

import SideBar from "../../../components/SideBar";
import DashboardHeader from "../../../components/DashboardHeader";
import { useTranslation } from "react-i18next";
import type { LinksFunction, MetaFunction } from "react-router";
import i18n from "i18next";
import Table from "../../../components/Table";
import type { Column } from "../../../types/TableType";

export const meta: MetaFunction = () => [
  { title: i18n.t("metaTitle", { title: i18n.t("rewards", { ns: "navigation" }), ns: "common" }) },
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

export default function RewardList() {
  const { t } = useTranslation(["navigation", "list-view", "common"]);

  const columns: Column<Record<string, unknown>>[] = [
    { key: "id", label: t("columns.id", { ns: "list-view" }), sortable: true },
    {
      key: "title",
      label: t("columns.title", { ns: "list-view" }),
      sortable: false,
    },
    { key: "code", label: t("columns.code", { ns: "list-view" }), sortable: true },
    {
      key: "endDate",
      label: t("columns.endDate", { ns: "list-view" }),
      sortable: true,
      render: (value: unknown): string => {
        if (!value) return "";
        return new Date(value as string).toLocaleString();
      },
    },
  ];

  return (
    <div className="container">
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("rewards", { ns: "navigation" })} />
        <Table
          title={t("title.rewards", { ns: "list-view" })}
          columns={columns}
          apiEndpoint="/api/rewards"
          canAdd={false}
          canEdit={true}
          canDelete={false}
          canView={true}
        />
      </main>
    </div>
  );
}

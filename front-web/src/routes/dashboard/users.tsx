import SideBar from "../../components/SideBar";
import DashboardHeader from "../../components/DashboardHeader";
import { useTranslation } from "react-i18next";
import type { LinksFunction, MetaFunction } from "react-router";
import i18n from "i18next";
import Table from "../../components/Table";

export const meta: MetaFunction = () => [
  { title: i18n.t("metaTitle", { title: i18n.t("users", { ns: "navigation" }), ns: "common" }) },
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

export default function Users() {
  const { t } = useTranslation(["navigation", "list-view", "common"]);

  const columns = [
    { key: "id", label: t("columns.id", { ns: "list-view" }), sortable: true },
    { key: "firstname", label: t("columns.firstname", { ns: "list-view" }), sortable: true },
    { key: "lastname", label: t("columns.lastname", { ns: "list-view" }), sortable: true },
    { key: "email", label: t("columns.email", { ns: "list-view" }), sortable: true },
    { key: "company", label: t("columns.company", { ns: "list-view" }), sortable: true },
    {
      key: "roles",
      label: t("columns.roles", { ns: "list-view" }),
      render: (value: string[]) => value.join(", "),
      sortable: false,
    },
  ];

  return (
    <div className="container">
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("users", { ns: "navigation" })} />
        <Table
          title={t("title.users", { ns: "list-view" })}
          columns={columns}
          apiEndpoint="/api/users"
        />
      </main>
    </div>
  );
}

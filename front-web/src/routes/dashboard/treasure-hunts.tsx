import SideBar from "../../components/SideBar";
import DashboardHeader from "../../components/DashboardHeader";
import { useTranslation } from "react-i18next";
import type { LinksFunction, MetaFunction } from "react-router";
import i18n from "i18next";

export const meta: MetaFunction = () => [
  {
    title: i18n.t("metaTitle", {
      title: i18n.t("treasureHunts", { ns: "navigation" }),
      ns: "common",
    }),
  },
];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/ui/dashboard-header.css" },
  { rel: "stylesheet", href: "/assets/css/ui/sidebar.css" },
  { rel: "stylesheet", href: "/assets/css/ui/button.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
];

export default function TreasureHunts() {
  const { t } = useTranslation(["navigation", "common"]);
  return (
    <div className="container">
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("treasureHunts", { ns: "navigation" })} />
      </main>
    </div>
  );
}

import { useTranslation } from "react-i18next";
import SideBar from "../../components/SideBar";
import DashboardHeader from "../../components/DashboardHeader";
import StatCard from "../../components/StatsCard";
import Table from "../../components/Table";

export function meta() {
  return [{ title: "Tableau de bord | Lootopia" }];
}

export default function Dashboard() {
  const { t } = useTranslation(["common"]);

  return (
    <div className="container">
      <SideBar />
      <main className="main-content">
        <DashboardHeader title="Dashboard" />
        <div className="stats-grid">
          <StatCard
            icon={
              <svg
                className="card-icon"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
              </svg>
            }
            cardTitle="Revenus totaux"
            cardValue="45 231 €"
            cardDescription="+12% ce mois"
            classDescription="trend-positive"
          />
          <StatCard
            icon={
              <svg
                className="card-icon"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
            }
            cardTitle="Utilisateurs"
            cardValue="2 345"
            cardDescription="+8% ce mois"
            classDescription="trend-positive"
          />
          <StatCard
            icon={
              <svg
                className="card-icon"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
              </svg>
            }
            cardTitle="Commandes"
            cardValue="1 234"
            cardDescription="-3% ce mois"
            classDescription="trend-negative"
          />
          <StatCard
            icon={
              <svg
                className="card-icon"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
              >
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
              </svg>
            }
            cardTitle="Taux de conversion"
            cardValue="3.2%"
            cardDescription="+0.5% ce mois"
            classDescription="trend-positive"
          />
        </div>
        <Table />
      </main>
    </div>
  );
}

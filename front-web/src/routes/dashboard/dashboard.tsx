import { useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import type { MetaFunction, LinksFunction } from "react-router";
import SideBar from "../../components/SideBar";
import DashboardHeader from "../../components/DashboardHeader";
import StatCard from "../../components/StatsCard";
import globalI18n from "i18next";
import { api } from "../../services/auth";
import {
  ResponsiveContainer,
  LineChart,
  Line,
  BarChart,
  Bar,
  PieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
} from "recharts";

type DashboardStats = {
  totalUsers?: number;
  totalHunts?: number;
  totalCompanies?: number;
  totalHuntsCreated?: number;
  totalUniqueParticipants?: number;
  totalRewardsClaimed?: number;
};

type ChartDataPoint = {
  name: string;
  value: number;
};

type DashboardCharts = {
  registrations?: ChartDataPoint[];
  categoryDistribution?: ChartDataPoint[];
  rarityDistribution?: ChartDataPoint[];
};

export const meta: MetaFunction = () => [
  {
    title: globalI18n.t("metaTitle", {
      title: globalI18n.t("dashboard", { ns: "navigation" }),
      ns: "common",
    }),
  },
];

export const links: LinksFunction = () => [
  { rel: "stylesheet", href: "/assets/css/ui/dashboard-header.css" },
  { rel: "stylesheet", href: "/assets/css/ui/sidebar.css" },
  { rel: "stylesheet", href: "/assets/css/ui/button.css" },
  { rel: "stylesheet", href: "/assets/css/ui/toast.css" },
  { rel: "stylesheet", href: "/assets/css/ui/table.css" },
  { rel: "stylesheet", href: "/assets/css/ui/stats-card.css" },
  { rel: "stylesheet", href: "/assets/css/ui/pagination.css" },
  { rel: "stylesheet", href: "/assets/css/ui/stats.css" },
];

const COLORS = ["#d4af37", "#7c3aed", "#3b82f6", "#10b981", "#ef4444", "#f97316"];

export default function Dashboard() {
  const { t, i18n } = useTranslation(["navigation", "common", "stats"]);
  const currentLanguage = i18n.language;

  const [isAdmin, setIsAdmin] = useState(false);

  const [stats, setStats] = useState<DashboardStats | null>(null);
  const [chartsData, setChartsData] = useState<DashboardCharts | null>(null);

  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        const userRes = await api.get("/api/auth/me");
        const userIsAdmin = userRes.data.roles?.includes("ROLE_ADMIN");
        setIsAdmin(userIsAdmin);

        const locale = currentLanguage?.split("-")[0] || "fr";

        const statsEndpoint = userIsAdmin ? "/api/statistics/admin" : "/api/statistics/company";
        const chartsEndpoint = userIsAdmin
          ? `/api/statistics/admin/charts?locale=${locale}`
          : `/api/statistics/company/charts?locale=${locale}`;

        const [statsRes, chartsRes] = await Promise.all([
          api.get(statsEndpoint),
          api.get(chartsEndpoint),
        ]);

        setStats(statsRes.data);
        setChartsData(chartsRes.data);
      } catch (error) {
        console.error("Error fetching dashboard data:", error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchDashboardData();
  }, [currentLanguage]);

  return (
    <div className="container">
      <SideBar />
      <main className="main-content">
        <DashboardHeader title={t("dashboard", { ns: "navigation" })} />

        {isLoading ? (
          <div className="loading-container">
            <svg
              className="spinning-icon"
              width="32"
              height="32"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              strokeLinecap="round"
              strokeLinejoin="round"
            >
              <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
            </svg>
          </div>
        ) : (
          <>
            <div className="stats-grid">
              {isAdmin ? (
                <>
                  <StatCard
                    icon={
                      <svg
                        className="card-icon"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                      >
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                      </svg>
                    }
                    cardTitle={t("totalUsers", { ns: "stats" })}
                    cardValue={stats?.totalUsers?.toLocaleString() || "0"}
                    cardDescription={t("usersDesc", { ns: "stats" })}
                    classDescription="trend-neutral"
                  />
                  <StatCard
                    icon={
                      <svg
                        className="card-icon"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                      >
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="6"></circle>
                        <circle cx="12" cy="12" r="2"></circle>
                      </svg>
                    }
                    cardTitle={t("totalHunts", { ns: "stats" })}
                    cardValue={stats?.totalHunts?.toLocaleString() || "0"}
                    cardDescription={t("huntsDesc", { ns: "stats" })}
                    classDescription="trend-neutral"
                  />
                  <StatCard
                    icon={
                      <svg
                        className="card-icon"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                      >
                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                        <path d="M9 22v-4h6v4"></path>
                        <path d="M8 6h.01"></path>
                        <path d="M16 6h.01"></path>
                        <path d="M12 6h.01"></path>
                        <path d="M12 10h.01"></path>
                        <path d="M12 14h.01"></path>
                        <path d="M16 10h.01"></path>
                        <path d="M16 14h.01"></path>
                        <path d="M8 10h.01"></path>
                        <path d="M8 14h.01"></path>
                      </svg>
                    }
                    cardTitle={t("totalCompanies", { ns: "stats" })}
                    cardValue={stats?.totalCompanies?.toLocaleString() || "0"}
                    cardDescription={t("companiesDesc", { ns: "stats" })}
                    classDescription="trend-neutral"
                  />
                </>
              ) : (
                <>
                  <StatCard
                    icon={
                      <svg
                        className="card-icon"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                      >
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="6"></circle>
                        <circle cx="12" cy="12" r="2"></circle>
                      </svg>
                    }
                    cardTitle={t("huntsCreated", { ns: "stats" })}
                    cardValue={stats?.totalHuntsCreated?.toLocaleString() || "0"}
                    cardDescription={t("companyHuntsDesc", { ns: "stats" })}
                    classDescription="trend-neutral"
                  />
                  <StatCard
                    icon={
                      <svg
                        className="card-icon"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                      >
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                      </svg>
                    }
                    cardTitle={t("uniqueParticipants", { ns: "stats" })}
                    cardValue={stats?.totalUniqueParticipants?.toLocaleString() || "0"}
                    cardDescription={t("participantsDesc", { ns: "stats" })}
                    classDescription="trend-positive"
                  />
                  <StatCard
                    icon={
                      <svg
                        className="card-icon"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        strokeWidth="2"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                      >
                        <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"></path>
                        <path d="M13 5v2"></path>
                        <path d="M13 17v2"></path>
                        <path d="M13 11v2"></path>
                      </svg>
                    }
                    cardTitle={t("rewardsClaimed", { ns: "stats" })}
                    cardValue={stats?.totalRewardsClaimed?.toLocaleString() || "0"}
                    cardDescription={t("rewardsDesc", { ns: "stats" })}
                    classDescription="trend-positive"
                  />
                </>
              )}
            </div>

            {chartsData && (
              <div className="charts-grid">
                {isAdmin ? (
                  <>
                    <div className="chart-card">
                      <h3 className="chart-title">{t("registrationsChart", { ns: "stats" })}</h3>
                      <div className="chart-container">
                        <ResponsiveContainer>
                          <LineChart data={chartsData.registrations}>
                            <CartesianGrid
                              strokeDasharray="3 3"
                              vertical={false}
                              stroke="#e5e7eb"
                            />
                            <XAxis
                              dataKey="name"
                              stroke="#6b7280"
                              fontSize={12}
                              tickLine={false}
                              axisLine={false}
                            />
                            <YAxis
                              stroke="#6b7280"
                              fontSize={12}
                              tickLine={false}
                              axisLine={false}
                              allowDecimals={false}
                            />
                            <Tooltip
                              contentStyle={{
                                borderRadius: "8px",
                                border: "none",
                                boxShadow: "0 4px 6px rgba(0,0,0,0.1)",
                              }}
                            />
                            <Line
                              type="monotone"
                              dataKey="value"
                              stroke="var(--gold, #d4af37)"
                              strokeWidth={3}
                              dot={{ r: 4, fill: "var(--gold, #d4af37)" }}
                              activeDot={{ r: 6 }}
                              name={t("newAgents", { ns: "stats" })}
                            />
                          </LineChart>
                        </ResponsiveContainer>
                      </div>
                    </div>

                    <div className="chart-card">
                      <h3 className="chart-title">{t("categoriesChart", { ns: "stats" })}</h3>
                      <div className="chart-container">
                        <ResponsiveContainer>
                          <PieChart>
                            <Pie
                              data={chartsData.categoryDistribution}
                              cx="50%"
                              cy="50%"
                              innerRadius={60}
                              outerRadius={100}
                              paddingAngle={5}
                              dataKey="value"
                            >
                              {chartsData.categoryDistribution?.map(
                                (_: ChartDataPoint, index: number) => (
                                  <Cell
                                    key={`cell-${index}`}
                                    fill={COLORS[index % COLORS.length]}
                                  />
                                )
                              )}
                            </Pie>
                            <Tooltip
                              contentStyle={{
                                borderRadius: "8px",
                                border: "none",
                                boxShadow: "0 4px 6px rgba(0,0,0,0.1)",
                              }}
                            />
                            <Legend verticalAlign="bottom" height={50} iconType="circle" />
                          </PieChart>
                        </ResponsiveContainer>
                      </div>
                    </div>
                  </>
                ) : (
                  <>
                    <div className="chart-card">
                      <h3 className="chart-title">
                        {t("companyCategoriesChart", { ns: "stats" })}
                      </h3>
                      <div className="chart-container">
                        <ResponsiveContainer>
                          <PieChart>
                            <Pie
                              data={chartsData.categoryDistribution}
                              cx="50%"
                              cy="50%"
                              innerRadius={60}
                              outerRadius={100}
                              paddingAngle={5}
                              dataKey="value"
                            >
                              {chartsData.categoryDistribution?.map(
                                (_: ChartDataPoint, index: number) => (
                                  <Cell
                                    key={`cell-${index}`}
                                    fill={COLORS[index % COLORS.length]}
                                  />
                                )
                              )}
                            </Pie>
                            <Tooltip
                              contentStyle={{
                                borderRadius: "8px",
                                border: "none",
                                boxShadow: "0 4px 6px rgba(0,0,0,0.1)",
                              }}
                            />
                            <Legend verticalAlign="bottom" height={36} iconType="circle" />
                          </PieChart>
                        </ResponsiveContainer>
                      </div>
                    </div>

                    <div className="chart-card">
                      <h3 className="chart-title">{t("companyRarityChart", { ns: "stats" })}</h3>
                      <div className="chart-container">
                        <ResponsiveContainer>
                          <BarChart data={chartsData.rarityDistribution}>
                            <CartesianGrid
                              strokeDasharray="3 3"
                              vertical={false}
                              stroke="#e5e7eb"
                            />
                            <XAxis
                              dataKey="name"
                              stroke="#6b7280"
                              fontSize={12}
                              tickLine={false}
                              axisLine={false}
                            />
                            <YAxis
                              stroke="#6b7280"
                              fontSize={12}
                              tickLine={false}
                              axisLine={false}
                              allowDecimals={false}
                            />
                            <Tooltip
                              cursor={{ fill: "rgba(0,0,0,0.02)" }}
                              contentStyle={{
                                borderRadius: "8px",
                                border: "none",
                                boxShadow: "0 4px 6px rgba(0,0,0,0.1)",
                              }}
                            />
                            <Bar
                              dataKey="value"
                              fill="var(--gold, #d4af37)"
                              radius={[4, 4, 0, 0]}
                              name={t("huntsCount", { ns: "stats" })}
                              barSize={40}
                            />
                          </BarChart>
                        </ResponsiveContainer>
                      </div>
                    </div>
                  </>
                )}
              </div>
            )}
          </>
        )}
      </main>
    </div>
  );
}

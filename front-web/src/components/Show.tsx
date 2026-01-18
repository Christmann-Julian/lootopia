import { useState, useEffect } from "react";
import { useTranslation } from "react-i18next";
import Toast from "./Toast";
import { Link } from "react-router";

export type ShowProps = {
  title: string;
  data: Record<string, string | number | boolean> | { error: string };
  backUrl?: string;
  editUrl?: string;
};

export default function Show({ title, data, backUrl, editUrl }: ShowProps) {
  const { t } = useTranslation(["show", "common"]);
  const [toast, setToast] = useState<{
    message: string;
    type: "success" | "error" | "info" | "warning";
  } | null>(null);

  useEffect(() => {
    if ("error" in data) {
      setToast({
        message: data.error as string,
        type: "error",
      });
    }
  }, [data]);

  const dataToShow = "error" in data ? {} : data;

  return (
    <>
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}
      <div className="detail-grid">
        <div style={{ display: "flex", flexDirection: "column", gap: "24px" }}>
          <div className="card">
            <div className="card-header">
              <h2 className="card-title">{title}</h2>
            </div>
            <div className="card-content">
              <div className="info-grid">
                {Object.entries(dataToShow).map(([key, value]) => (
                  <div className="info-item" key={key}>
                    <span className="info-label">{t(key, { ns: "show" })}</span>
                    <span className="info-value">
                      {typeof value === "boolean" ? (
                        value ? (
                          <span className="badge badge-success">
                            {t("badge.true", { ns: "show" })}
                          </span>
                        ) : (
                          <span className="badge badge-destructive">
                            {t("badge.false", { ns: "show" })}
                          </span>
                        )
                      ) : (
                        value
                      )}
                    </span>
                  </div>
                ))}
              </div>
            </div>
          </div>
        </div>
        <div style={{ display: "flex", flexDirection: "column", gap: "24px" }}>
          <div className="card">
            <div className="card-header">
              <h2 className="card-title">{t("action.title", { ns: "show" })}</h2>
            </div>
            <div
              className="card-content"
              style={{ display: "flex", flexDirection: "column", gap: "12px" }}
            >
              <Link to={editUrl || "#"} className="button button-primary">
                <svg
                  width="16"
                  height="16"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                >
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                {t("action.edit", { ns: "show" })}
              </Link>
              <Link to={backUrl || "#"} className="button button-outline">
                <svg
                  width="14"
                  height="14"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2"
                >
                  <line x1="19" y1="12" x2="5" y2="12"></line>
                  <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                {t("action.back", { ns: "show" })}
              </Link>
            </div>
          </div>
        </div>
      </div>
    </>
  );
}

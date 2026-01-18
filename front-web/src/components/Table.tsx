import { Link } from "react-router";
import { useState, useEffect, useCallback } from "react";
import Pagination from "./Pagination";
import ConfirmationDialog from "./ConfirmationDialog";
import Toast from "./Toast";
import { api } from "../services/auth";
import { useTranslation } from "react-i18next";
import type { TableProps, TableRow, MetaData } from "../types/TableType";

export default function Table({ title, columns, apiEndpoint }: TableProps) {
  const { t } = useTranslation(["table", "common"]);

  const [data, setData] = useState<TableRow[]>([]);
  const [meta, setMeta] = useState<MetaData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [toast, setToast] = useState<{ message: string; type: "success" | "error" } | null>(null);

  const [selectedRowId, setSelectedRowId] = useState<number | null>(null);
  const [showConfirm, setShowConfirm] = useState(false);
  const [selectedIds, setSelectedIds] = useState<number[]>([]);
  const [isBulkDeleteConfirm, setIsBulkDeleteConfirm] = useState(false);

  const [localParams, setLocalParams] = useState({
    page: 1,
    limit: 10,
    sort: "id",
    direction: "asc",
    q: "",
  });

  const currentSort = localParams.sort;
  const currentDirection = localParams.direction;
  const currentSearch = localParams.q;

  const [localSearch, setLocalSearch] = useState(currentSearch);

  const fetchData = useCallback(async () => {
    setIsLoading(true);
    const params = new URLSearchParams(
      Object.entries(localParams).reduce(
        (acc, [key, value]) => {
          acc[key] = String(value);
          return acc;
        },
        {} as Record<string, string>
      )
    );

    try {
      const response = await api.get(`${apiEndpoint}?${params.toString()}`);
      setData(response.data.data);
      setMeta(response.data.meta);
    } catch {
      setToast({ message: t("internalServerError", { ns: "common" }), type: "error" });
      setData([]);
    } finally {
      setIsLoading(false);
    }
  }, [localParams, apiEndpoint, t]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  useEffect(() => {
    setSelectedIds([]);
  }, [data]);

  const handleDeleteRow = async (rowId: number) => {
    try {
      await api.delete(`${apiEndpoint}/${rowId}`);
      setToast({ message: t("deleteSuccess", { ns: "table" }), type: "success" });
      fetchData();
    } catch {
      setToast({ message: t("deleteError", { ns: "table" }), type: "error" });
    }
  };

  const handleBulkDelete = async (ids: number[]) => {
    try {
      await Promise.all(ids.map((id) => api.delete(`${apiEndpoint}/${id}`)));
      setToast({
        message: t("deleteMultipleSuccess", { ns: "table", count: ids.length }),
        type: "success",
      });
      fetchData();
      setSelectedIds([]);
    } catch {
      setToast({ message: t("deleteError", { ns: "table" }), type: "error" });
    }
  };

  const updateParams = useCallback((newParams: Partial<typeof localParams>) => {
    setLocalParams((prev) => ({ ...prev, ...newParams }));
  }, []);

  useEffect(() => {
    setLocalSearch(currentSearch);
  }, [currentSearch]);

  useEffect(() => {
    const timeoutId = setTimeout(() => {
      if (localSearch !== localParams.q) {
        updateParams({ q: localSearch, page: 1 });
      }
    }, 1000);
    return () => clearTimeout(timeoutId);
  }, [localSearch, localParams.q, updateParams]);

  const handleSort = (key: string) => {
    const newDirection =
      localParams.sort === key && localParams.direction === "asc" ? "desc" : "asc";
    updateParams({ sort: key, direction: newDirection, page: 1 });
  };

  const handlePageChange = (page: number) => {
    updateParams({ page });
  };

  const handleSelectAll = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.checked) setSelectedIds(data.map((row) => row.id));
    else setSelectedIds([]);
  };

  const handleSelectRow = (id: number) => {
    if (selectedIds.includes(id)) setSelectedIds(selectedIds.filter((item) => item !== id));
    else setSelectedIds([...selectedIds, id]);
  };

  const handleDeleteClick = (rowId: number) => {
    setSelectedRowId(rowId);
    setIsBulkDeleteConfirm(false);
    setShowConfirm(true);
  };

  const handleBulkDeleteClick = () => {
    setIsBulkDeleteConfirm(true);
    setShowConfirm(true);
  };

  const confirmDelete = () => {
    if (isBulkDeleteConfirm) {
      handleBulkDelete(selectedIds);
    } else if (selectedRowId !== null) {
      handleDeleteRow(selectedRowId);
    }
    setShowConfirm(false);
    setSelectedRowId(null);
    setIsBulkDeleteConfirm(false);
  };

  const cancelDelete = () => {
    setShowConfirm(false);
    setSelectedRowId(null);
    setIsBulkDeleteConfirm(false);
  };

  const slugify = (text: string) =>
    text
      .toString()
      .toLowerCase()
      .trim()
      .replace(/\s+/g, "-")
      .replace(/[^\w-]+/g, "");

  const generateCsv = (rowsToExport: Record<string, unknown>[]) => {
    const csvContent = [
      columns.map((col) => col.label).join(","),
      ...rowsToExport.map((row) =>
        columns
          .map((col) => {
            const value = row[col.key];
            return typeof value === "string" ? `"${value.replace(/"/g, '""')}"` : value;
          })
          .join(",")
      ),
    ].join("\n");
    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", `${slugify(title)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  return (
    <div className="table-container">
      {toast && <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />}
      <div className="table-header">
        <h2 className="table-title">{title}</h2>
        <div className="toolbar">
          <div className="search-wrapper">
            <svg
              className="search-icon"
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
            >
              <circle cx="11" cy="11" r="8"></circle>
              <path d="m21 21-4.35-4.35"></path>
            </svg>
            <input
              type="text"
              className="input"
              placeholder={t("searchPlaceholder", { ns: "table" })}
              value={localSearch}
              onChange={(e) => setLocalSearch(e.target.value)}
            />
          </div>
          <button className="button button-outline" onClick={() => generateCsv(data)}>
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
            >
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="7 10 12 15 17 10"></polyline>
              <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            {t("exportBtn", { ns: "table" })}
          </button>
          <Link to="create" className="button button-primary">
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
            >
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            {t("addBtn", { ns: "table" })}
          </Link>
        </div>

        <div
          className={`alert alert-info ${selectedIds.length > 0 ? "visible" : ""}`}
          style={{ display: selectedIds.length > 0 ? "flex" : "none" }}
        >
          <span>{t("alertInfoSelected", { count: selectedIds.length, ns: "table" })}</span>
          <div className="alert-actions">
            <button
              className="button button-primary"
              onClick={() => generateCsv(data.filter((r) => selectedIds.includes(r.id)))}
            >
              {t("exportMultipleBtn", { ns: "table" })}
            </button>
            <button className="button button-primary" onClick={handleBulkDeleteClick}>
              {t("deleteBtn", { ns: "table" })} ({selectedIds.length})
            </button>
          </div>
        </div>
      </div>

      <div className="table-wrapper">
        <table id="dataTable">
          <thead>
            <tr>
              <th>
                <input
                  type="checkbox"
                  className="checkbox"
                  checked={data.length > 0 && selectedIds.length === data.length}
                  onChange={handleSelectAll}
                  disabled={data.length === 0}
                />
              </th>
              {columns.map((column) => (
                <th
                  key={column.key}
                  onClick={() => column.sortable && handleSort(column.key)}
                  style={{ cursor: column.sortable ? "pointer" : "default", userSelect: "none" }}
                >
                  <div style={{ display: "flex", alignItems: "center", gap: "6px" }}>
                    {column.label}
                    {currentSort === column.key && (
                      <span style={{ fontSize: "0.8em" }}>
                        {currentDirection === "asc" ? " ▲" : " ▼"}
                      </span>
                    )}
                  </div>
                </th>
              ))}
              <th style={{ width: "120px" }}>{t("columnActions", { ns: "table" })}</th>
            </tr>
          </thead>
          <tbody>
            {isLoading ? (
              <tr>
                <td
                  colSpan={columns.length + 3}
                  style={{ textAlign: "center", padding: "40px", color: "#666" }}
                >
                  <div className="loading-spinner">{t("tableLoading", { ns: "table" })}</div>
                </td>
              </tr>
            ) : data.length > 0 ? (
              data.map((row, rowIndex) => (
                <tr key={rowIndex} className={selectedIds.includes(row.id) ? "selected-row" : ""}>
                  <td>
                    <input
                      type="checkbox"
                      className="checkbox row-select"
                      checked={selectedIds.includes(row.id)}
                      onChange={() => handleSelectRow(row.id)}
                    />
                  </td>
                  {columns.map((column) => (
                    <td key={column.key}>
                      {column.render
                        ? column.render(row[column.key] as string, row)
                        : (row[column.key] as string)}
                    </td>
                  ))}
                  <td>
                    <div className="actions">
                      <Link
                        to={`${row.id}/show`}
                        className="button button-ghost button-icon view"
                        title="Voir"
                      >
                        <svg
                          width="16"
                          height="16"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="2"
                        >
                          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                          <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                      </Link>
                      <Link
                        to={`${row.id}/edit`}
                        className="button button-ghost button-icon edit"
                        title="Modifier"
                      >
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
                      </Link>
                      <button
                        className="button button-ghost button-icon delete"
                        title="Supprimer"
                        onClick={() => handleDeleteClick(row.id)}
                      >
                        <svg
                          width="16"
                          height="16"
                          viewBox="0 0 24 24"
                          fill="none"
                          stroke="currentColor"
                          strokeWidth="2"
                        >
                          <polyline points="3 6 5 6 21 6"></polyline>
                          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              ))
            ) : (
              <tr>
                <td
                  colSpan={columns.length + 3}
                  style={{ textAlign: "center", padding: "24px", color: "#666" }}
                >
                  {t("noResults", { ns: "table" })}
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {meta && (
        <Pagination
          currentPage={meta.page}
          totalPages={Math.ceil(meta.total / meta.limit)}
          totalItems={meta.total}
          limit={meta.limit}
          onPageChange={handlePageChange}
        />
      )}

      {showConfirm && (
        <ConfirmationDialog
          message={
            isBulkDeleteConfirm
              ? t("confirmDelete.messageMultiple", { count: selectedIds.length, ns: "table" })
              : t("confirmDelete.message", { ns: "table" })
          }
          onConfirm={confirmDelete}
          onCancel={cancelDelete}
        />
      )}
    </div>
  );
}

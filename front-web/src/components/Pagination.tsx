import { Trans } from "react-i18next";

type PaginationProps = {
  currentPage: number;
  totalPages: number;
  totalItems: number;
  limit: number;
  onPageChange: (page: number) => void;
};

export default function Pagination({
  currentPage,
  totalPages,
  totalItems,
  limit,
  onPageChange,
}: PaginationProps) {
  const startItem = totalItems === 0 ? 0 : (currentPage - 1) * limit + 1;
  const endItem = Math.min(currentPage * limit, totalItems);

  const getPageNumbers = () => {
    const pages = [];

    if (totalPages <= 7) {
      for (let i = 1; i <= totalPages; i++) {
        pages.push(i);
      }
    } else {
      pages.push(1);

      if (currentPage > 3) {
        pages.push("...");
      }

      const startRange = Math.max(2, currentPage - 1);
      const endRange = Math.min(totalPages - 1, currentPage + 1);

      for (let i = startRange; i <= endRange; i++) {
        pages.push(i);
      }

      if (currentPage < totalPages - 2) {
        pages.push("...");
      }

      pages.push(totalPages);
    }
    return pages;
  };

  if (totalItems === 0) return null;

  return (
    <div className="pagination">
      <div className="pagination-info">
        <Trans
          i18nKey="pagination.displayingResults"
          ns="table"
          components={{
            strong: <strong />,
          }}
          values={{
            startItem,
            endItem,
            totalItems,
          }}
        />
      </div>

      <div className="pagination-buttons">
        <button
          className="page-button"
          disabled={currentPage === 1}
          onClick={() => onPageChange(currentPage - 1)}
        >
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
          >
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>

        {getPageNumbers().map((page, index) => {
          if (page === "...") {
            return (
              <span
                key={`dots-${index}`}
                className="page-button dots"
                style={{ border: "none", cursor: "default" }}
              >
                ...
              </span>
            );
          }

          return (
            <button
              key={page}
              className={`page-button ${currentPage === page ? "active" : ""}`}
              onClick={() => onPageChange(page as number)}
            >
              {page}
            </button>
          );
        })}

        <button
          className="page-button"
          disabled={currentPage === totalPages}
          onClick={() => onPageChange(currentPage + 1)}
        >
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
          >
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>
    </div>
  );
}

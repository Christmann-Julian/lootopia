export default function Pagination() {
  return (
    <div className="pagination">
      <div className="pagination-info">Affichage de 1 à 5 sur 24 résultats</div>
      <div className="pagination-buttons">
        <button className="page-button" disabled>
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
        <button className="page-button active">1</button>
        <button className="page-button">2</button>
        <button className="page-button">...</button>
        <button className="page-button">10</button>
        <button className="page-button">
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

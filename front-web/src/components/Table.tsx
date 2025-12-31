import Pagination from "./Pagination";

export default function Table() {
  return (
    <div className="table-container">
      <div className="table-header">
        <h2 className="table-title">Liste des commandes</h2>

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
            <input type="text" className="input" id="searchInput" placeholder="Rechercher..." />
          </div>
          <button className="button button-outline" id="filterBtn">
            <svg
              width="16"
              height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
            >
              <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            Filtrer
          </button>
          <button className="button button-outline" id="exportBtn">
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
            Export
          </button>
          <button className="button button-primary" id="newOrderBtn">
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
            Nouvelle commande
          </button>
        </div>

        <div className="alert alert-info" id="bulkActions">
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
          >
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
          </svg>
          <span id="selectedCount">0 lignes sélectionnées</span>
          <div className="alert-actions">
            <button className="button button-ghost" id="bulkEditBtn">
              Modifier
            </button>
            <button className="button button-ghost" id="bulkDeleteBtn">
              Supprimer
            </button>
          </div>
        </div>
      </div>

      <div className="table-wrapper">
        <table id="dataTable">
          <thead>
            <tr>
              <th style={{ width: "40px" }}>
                <input type="checkbox" className="checkbox" id="selectAll" />
              </th>
              <th className="sortable" data-column="id">
                ID
              </th>
              <th className="sortable" data-column="client">
                Client
              </th>
              <th className="sortable" data-column="email">
                Email
              </th>
              <th className="sortable" data-column="montant">
                Montant
              </th>
              <th className="sortable" data-column="statut">
                Statut
              </th>
              <th className="sortable" data-column="date">
                Date
              </th>
              <th style={{ width: "120px" }}>Actions</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <tr>
              <td>
                <input type="checkbox" className="checkbox row-select" />
              </td>
              <td data-column="id">
                <span className="badge badge-outline">#001</span>
              </td>
              <td data-column="client">Jean Dupont</td>
              <td data-column="email">jean.dupont@email.com</td>
              <td data-column="montant">125,00 €</td>
              <td data-column="statut">
                <span className="badge badge-success">Livrée</span>
              </td>
              <td data-column="date">12 déc 2025</td>
              <td>
                <div className="actions">
                  <button className="button button-ghost button-icon view" title="Voir">
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
                  </button>
                  <button className="button button-ghost button-icon edit" title="Modifier">
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
                  </button>
                  <button className="button button-ghost button-icon delete" title="Supprimer">
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
            <tr>
              <td>
                <input type="checkbox" className="checkbox row-select" />
              </td>
              <td data-column="id">
                <span className="badge badge-outline">#002</span>
              </td>
              <td data-column="client">Marie Martin</td>
              <td data-column="email">marie.martin@email.com</td>
              <td data-column="montant">89,50 €</td>
              <td data-column="statut">
                <span className="badge badge-warning">En cours</span>
              </td>
              <td data-column="date">12 déc 2025</td>
              <td>
                <div className="actions">
                  <button className="button button-ghost button-icon view">
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
                  </button>
                  <button className="button button-ghost button-icon edit">
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
                  </button>
                  <button className="button button-ghost button-icon delete">
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
            <tr>
              <td>
                <input type="checkbox" className="checkbox row-select" />
              </td>
              <td data-column="id">
                <span className="badge badge-outline">#003</span>
              </td>
              <td data-column="client">Pierre Durand</td>
              <td data-column="email">pierre.durand@email.com</td>
              <td data-column="montant">256,00 €</td>
              <td data-column="statut">
                <span className="badge badge-success">Livrée</span>
              </td>
              <td data-column="date">11 déc 2025</td>
              <td>
                <div className="actions">
                  <button className="button button-ghost button-icon view">
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
                  </button>
                  <button className="button button-ghost button-icon edit">
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
                  </button>
                  <button className="button button-ghost button-icon delete">
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
            <tr>
              <td>
                <input type="checkbox" className="checkbox row-select" />
              </td>
              <td data-column="id">
                <span className="badge badge-outline">#004</span>
              </td>
              <td data-column="client">Sophie Bernard</td>
              <td data-column="email">sophie.bernard@email.com</td>
              <td data-column="montant">45,00 €</td>
              <td data-column="statut">
                <span className="badge badge-destructive">Annulée</span>
              </td>
              <td data-column="date">11 déc 2025</td>
              <td>
                <div className="actions">
                  <button className="button button-ghost button-icon view">
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
                  </button>
                  <button className="button button-ghost button-icon edit">
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
                  </button>
                  <button className="button button-ghost button-icon delete">
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
            <tr>
              <td>
                <input type="checkbox" className="checkbox row-select" />
              </td>
              <td data-column="id">
                <span className="badge badge-outline">#005</span>
              </td>
              <td data-column="client">Luc Petit</td>
              <td data-column="email">luc.petit@email.com</td>
              <td data-column="montant">178,00 €</td>
              <td data-column="statut">
                <span className="badge badge-warning">En cours</span>
              </td>
              <td data-column="date">10 déc 2025</td>
              <td>
                <div className="actions">
                  <button className="button button-ghost button-icon view">
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
                  </button>
                  <button className="button button-ghost button-icon edit">
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
                  </button>
                  <button className="button button-ghost button-icon delete">
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
          </tbody>
        </table>
      </div>
      <Pagination />
    </div>
  );
}

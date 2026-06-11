# Fonctionnalités de l'interface d'administration

Ce document décrit l'architecture des pages de l'admin web et les composants génériques qui les composent. Chaque ressource (hunts, badges, users, etc.) suit le même patron CRUD.

---

## Architecture des pages

Chaque ressource expose quatre vues suivant toujours la même structure :

```
dashboard/{resource}/
├── index          ->  HuntList    (liste paginée)
├── create         ->  HuntCreate  (formulaire de création)
├── {id}/show      ->  HuntShow    (détail en lecture seule)
└── {id}/edit      ->  HuntEdit    (formulaire de modification)
```

---

## Composants génériques

### Table

Composant central de toutes les pages de liste. Il gère lui-même la récupération des données, la pagination, le tri, la recherche et les actions.

```tsx
<Table
  title="Chasses"
  columns={columns}
  apiEndpoint="/api/hunts"
  canAdd={true}
  canEdit={true}
  canDelete={true}
  canView={true}
/>
```

**Props :**

| Prop | Type | Défaut | Description |
|---|---|---|---|
| `title` | string | - | Titre affiché et nom du fichier CSV exporté |
| `columns` | Column[] | - | Définition des colonnes |
| `apiEndpoint` | string | - | Préfixe de l'endpoint (`/api/hunts` -> appelle `/api/hunts/admin`) |
| `canAdd` | boolean | `true` | Affiche le bouton "Ajouter" (lien vers `create`) |
| `canEdit` | boolean | `true` | Affiche l'icône de modification (lien vers `{id}/edit`) |
| `canDelete` | boolean | `true` | Affiche l'icône de suppression et le bouton de suppression multiple |
| `canView` | boolean | `true` | Affiche l'icône de visualisation (lien vers `{id}/show`) |

**Définition d'une colonne :**

```ts
type Column = {
  key: string;       // Clé dans l'objet de données
  label: string;     // En-tête de colonne
  sortable: boolean; // Active le tri au clic sur l'en-tête
  render?: (value: string, row: TableRow) => ReactNode; // Rendu personnalisé de la cellule
}
```

**Fonctionnalités intégrées :**

- **Recherche** - champ texte avec debounce de 1 seconde avant d'appeler l'API. Remet la pagination à la page 1.
- **Tri** - clic sur un en-tête de colonne `sortable`. Alterne entre `asc` et `desc`. Remet la pagination à la page 1.
- **Pagination** - gérée via le composant `Pagination`, décrit ci-dessous.
- **Sélection multiple** - cases à cocher par ligne + case "tout sélectionner" dans l'en-tête.
- **Export CSV** - exporte les données courantes (page visible) ou uniquement les lignes sélectionnées. Le fichier est nommé d'après le `title` slugifié.
- **Suppression unitaire** - déclenche un `ConfirmationDialog`, puis `DELETE {apiEndpoint}/{id}` et recharge les données.
- **Suppression multiple** - déclenche un `ConfirmationDialog`, puis envoie les `DELETE` en parallèle via `Promise.all`.

**Paramètres envoyés à l'API :**

```
GET {apiEndpoint}/admin?page=1&limit=10&sort=id&direction=asc&q=
```

Ces paramètres correspondent exactement aux paramètres de pagination des routes `/admin` de l'API (voir [endpoints.md](../api/endpoints.md)).

---

### Show

Composant générique pour les pages de détail en lecture seule.

```tsx
<Show
  title="Détail de la chasse"
  data={data}
  backUrl={`/${lang}/dashboard/hunts`}
  editUrl={`/${lang}/dashboard/hunts/${id}/edit`}
/>
```

**Props :**

| Prop | Type | Description |
|---|---|---|
| `title` | string | Titre de la card |
| `data` | Record<string, ...> ou `{ error: string }` | Données à afficher |
| `backUrl` | string | URL du bouton "Retour" |
| `editUrl` | string | URL du bouton "Modifier" |

Chaque clé de `data` est affichée comme une ligne `label / valeur`. Les labels sont traduits via le namespace `show` (`t(key, { ns: "show" })`). Les valeurs booléennes sont rendues en badge coloré (`badge-success` / `badge-destructive`). Si `data` contient un champ `error`, un toast d'erreur est affiché.

---

### Pagination

Calcule et affiche les boutons de page. Retourne `null` si `totalItems === 0`.

**Algorithme d'affichage des pages :**
- 7 pages ou moins : toutes les pages sont affichées.
- Plus de 7 pages : affiche la première page, les pages autour de la page courante (±1), la dernière page, et des `...` pour les intervalles.

Le compteur de résultats utilise le composant `Trans` pour formater `"X - Y sur Z résultats"` avec les valeurs en gras.

---

### Toast

Notification temporaire auto-fermante.

```tsx
<Toast
  message="Opération réussie"
  type="success"    // "success" | "error" | "info" | "warning"
  duration={4000}   // ms, défaut 4000
  onClose={() => setToast(null)}
/>
```

Se ferme automatiquement après `duration` ms ou manuellement via le bouton de fermeture. Le timer est nettoyé si le composant est démonté avant l'expiration.

---

## Patron CRUD par ressource

### Page de liste

La page de liste n'a **pas de `clientLoader`** - le composant `Table` gère entièrement la récupération des données.

```tsx
export default function HuntList() {
  const columns: Column[] = [
    { key: "id", label: "ID", sortable: true },
    { key: "title", label: "Titre", sortable: false },
    { key: "location", label: "Lieu", sortable: false },
  ];

  return (
    <Table
      title="Chasses"
      columns={columns}
      apiEndpoint="/api/hunts"
    />
  );
}
```

---

### Page de création

**`clientLoader`** : charge les dépendances nécessaires au formulaire (listes déroulantes) en parallèle avec `Promise.all`. Les appels utilisent `X-Skip-Locale: true` pour récupérer toutes les traductions disponibles (objet `translations`) plutôt que la traduction de la locale courante, afin de peupler les `<select>` avec les labels français.

```ts
const [categoriesRes, raritiesRes] = await Promise.all([
  api.get("/api/categories", { headers: { "X-Skip-Locale": "true" } }),
  api.get("/api/rarities", { headers: { "X-Skip-Locale": "true" } }),
]);
```

**`clientAction`** : `POST {apiEndpoint}`. En cas de succès : toast + `reset()` du formulaire (l'utilisateur reste sur la page de création). En cas d'erreur : toast avec le premier message d'erreur du champ `details`.

**Champs imbriqués** : les formulaires utilisent la notation pointée de React Hook Form pour construire des objets JSON complexes :

```tsx
// Produit { translations: { fr: { title: "..." }, en: { title: "..." } } }
register("translations.fr.title")
register("translations.en.title")

// Produit { reward: { code: "...", endDate: "..." } }
register("reward.code")
register("reward.endDate")
```

**Champs conditionnels** : `useCan` est utilisé pour conditionner l'affichage de certains champs selon le rôle. Exemple : le champ `isSponsor` n'est visible que pour les admins.

```tsx
{can("ROLE_ADMIN") && (
  <div className="form-group">
    <input type="checkbox" {...register("isSponsor")} />
  </div>
)}
```

---

### Page de modification

**`clientLoader`** : charge la ressource courante et ses dépendances en parallèle. La ressource est également chargée avec `X-Skip-Locale: true` pour récupérer toutes ses traductions. Les IDs des relations sont normalisés avant d'être passés à React Hook Form :

```ts
huntData.categoryId = huntData.category?.id || null;
huntData.rarityId = huntData.rarity?.id || null;
```

Le formulaire est pré-rempli via `defaultValues` :

```ts
useForm<EditHuntFormData>({
  defaultValues: loaderData.hunt,
});
```

**`clientAction`** : `PUT {apiEndpoint}/{id}`. L'`id` de la ressource est extrait des données du loader et ajouté au payload au moment du submit (pas de champ caché dans le formulaire) :

```ts
const dataWithId = { ...data, id: loaderData.hunt.id };
fetcher.submit(dataWithId, { method: "post", encType: "application/json" });
```

En cas de succès : toast (l'utilisateur reste sur la page de modification).

---

### Page de détail

**`clientLoader`** : `GET {apiEndpoint}/{id}` (avec locale auto-injectée par l'intercepteur Axios - les données retournées sont donc dans la langue courante).

Les données sont aplaties avant d'être passées à `Show` : les objets imbriqués sont réduits à leur représentation textuelle, les valeurs nulles sont remplacées par une valeur traduite `t("empty", { ns: "show" })` :

```ts
const data = {
  ...loaderData,
  category: loaderData.category?.name || t("empty", { ns: "show" }),
  rarity: loaderData.rarity?.name || t("empty", { ns: "show" }),
  reward: loaderData.reward?.code || t("empty", { ns: "show" }),
};
```

---

## Variables d'environnement

| Variable | Description |
|---|---|
| `VITE_API_URL` | URL de base de l'API Symfony (ex : `http://localhost:8000`) |

Voir [auth.md](auth.md) pour les détails sur la configuration Axios et le forçage HTTPS.
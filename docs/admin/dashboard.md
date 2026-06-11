# Dashboard

Le dashboard est la page d'accueil de l'interface d'administration. Son contenu s'adapte automatiquement selon le rôle de l'utilisateur connecté.

---

## Chargement des données

Contrairement aux pages de liste, le dashboard **n'utilise pas de `clientLoader`**. Toutes les données sont récupérées dans un `useEffect` au montage du composant, et à chaque changement de langue.

Le chargement se déroule en deux étapes :

**Étape 1 - Détermination du rôle :**

```ts
const userRes = await api.get("/api/auth/me");
const userIsAdmin = userRes.data.roles?.includes("ROLE_ADMIN");
```

L'appel à `/api/auth/me` est nécessaire ici car le rôle n'est pas lu depuis le JWT (le hook `useCan` n'est pas utilisé) mais depuis la réponse API.

**Étape 2 - Chargement des statistiques et graphiques en parallèle :**

```ts
const [statsRes, chartsRes] = await Promise.all([
  api.get(statsEndpoint),
  api.get(chartsEndpoint),
]);
```

Les endpoints appelés dépendent du rôle :

| Rôle | Endpoint stats | Endpoint charts |
|---|---|---|
| `ROLE_ADMIN` | `/api/statistics/admin` | `/api/statistics/admin/charts?locale={locale}` |
| `ROLE_USER` | `/api/statistics/company` | `/api/statistics/company/charts?locale={locale}` |

La locale est extraite de `i18n.language` et tronquée au code de base (`"fr-FR"` → `"fr"`). Elle est passée **manuellement** dans l'URL des charts - l'intercepteur Axios ne la double pas car l'URL contient déjà `locale=`.

Le `useEffect` se redéclenche à chaque changement de `currentLanguage`, ce qui recharge les graphiques dans la nouvelle langue.

---

## Statistiques

### Vue Admin

Trois métriques globales retournées par `/api/statistics/admin` :

| Métrique | Clé | Description |
|---|---|---|
| Utilisateurs | `totalUsers` | Nombre total de comptes |
| Chasses | `totalHunts` | Nombre total de chasses créées |
| Sociétés | `totalCompanies` | Nombre de sociétés enregistrées |

### Vue Entreprise

Trois métriques propres à la société retournées par `/api/statistics/company` :

| Métrique | Clé | Description |
|---|---|---|
| Chasses créées | `totalHuntsCreated` | Chasses créées par cette société |
| Participants uniques | `totalUniqueParticipants` | Joueurs distincts ayant participé |
| Récompenses réclamées | `totalRewardsClaimed` | Récompenses collectées par les joueurs |

### Composant StatCard

```tsx
<StatCard
  icon={<svg>...</svg>}
  cardTitle="Utilisateurs"
  cardValue="1 250"
  cardDescription="Nombre total de comptes"
  classDescription="trend-neutral"
/>
```

| Prop | Type | Description |
|---|---|---|
| `icon` | ReactNode | SVG affiché en haut à droite de la card |
| `cardTitle` | string | Libellé de la métrique |
| `cardValue` | string | Valeur formatée (`.toLocaleString()` appliqué avant passage) |
| `cardDescription` | string | Texte descriptif sous la valeur |
| `classDescription` | string | Classe CSS de la description : `trend-neutral`, `trend-positive` |

---

## Graphiques

Les graphiques sont rendus avec **Recharts** via `ResponsiveContainer` pour s'adapter à la largeur de leur conteneur.

### Vue Admin

**Graphique de registrations (LineChart)**
- Données : `chartsData.registrations` - tableau de `{ name: string, value: number }`
- `name` : libellé de la période (ex : mois)
- `value` : nombre de nouveaux utilisateurs
- Couleur de la ligne : `var(--gold, #d4af37)`

**Répartition par catégorie (PieChart - donut)**
- Données : `chartsData.categoryDistribution`
- `name` : nom de la catégorie (traduit selon la locale)
- `value` : nombre de chasses dans cette catégorie
- Couleurs issues de la palette fixe (voir ci-dessous)
- Légende positionnée en bas

### Vue Entreprise

**Répartition par catégorie (PieChart - donut)**
- Même structure que la vue admin, filtrée sur les chasses de la société.

**Répartition par rareté (BarChart)**
- Données : `chartsData.rarityDistribution`
- `name` : nom de la rareté (traduit selon la locale)
- `value` : nombre de chasses par rareté
- Couleur des barres : `var(--gold, #d4af37)`
- `barSize: 40`, coins arrondis en haut (`radius: [4, 4, 0, 0]`)

### Palette de couleurs (PieChart)

```ts
const COLORS = ["#d4af37", "#7c3aed", "#3b82f6", "#10b981", "#ef4444", "#f97316"];
```

Les couleurs sont assignées par index cyclique (`index % COLORS.length`).

---

## Gestion des états

| État | Comportement |
|---|---|
| Chargement | Spinner centré affiché à la place du contenu |
| Données chargées | Stats et graphiques affichés |
| `chartsData === null` | Section graphiques non rendue (guard `{chartsData && ...}`) |
| Erreur réseau | Loguée en console uniquement - aucun feedback utilisateur |

> Les erreurs de chargement du dashboard ne sont pas remontées à l'utilisateur. En cas de problème réseau ou d'API, la page reste vide après le spinner.

---

## Namespace i18n utilisés

| Namespace | Clés utilisées |
|---|---|
| `navigation` | `dashboard` (titre de la page) |
| `common` | `metaTitle` (balise `<title>`) |
| `stats` | Titres et descriptions de toutes les métriques et graphiques |
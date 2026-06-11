# Pages de la PWA

Ce document décrit les pages principales de l'application joueur : accueil, liste des chasses, et inventaire de récompenses.

---

## Home (`/home`)

Page d'accueil du joueur. Affiche son statut, la chasse sponsorisée mise en avant et une grille des autres chasses disponibles.

### Chargement des données

Deux flux indépendants lancés au montage, qui se redéclenchent à chaque changement de `i18n.language` :

```
// Flux 1 - données joueur (enchaînés)
GET /api/auth/me  ->  GET /api/users/{id}

// Flux 2 - chasses sponsorisées
GET /api/hunts/sponsored?limit=10
```

Les deux flux partent en parallèle mais ne sont pas dans un `Promise.all`. L'état `isLoading` est contrôlé uniquement par le `finally` du flux des chasses sponsorisées - les données joueur peuvent ne pas être disponibles quand le chargement se termine.

La dépendance à `i18n.language` provoque un re-fetch à chaque changement de langue pour mettre à jour le nom du rang traduit. Le nom du rang est lu directement depuis `rank.translations[i18n.language]` sans troncature du code langue (contrairement au pattern `.split("-")[0]` utilisé dans Profile et les autres pages).

### Mise en avant des chasses

La première chasse sponsorisée (`sponsoredHunts[0]`) est affichée dans la card principale avec un lien direct vers `/radar/{huntId}`. Les chasses suivantes (`sponsoredHunts.slice(1)`) apparaissent dans une grille secondaire.

Si aucune chasse sponsorisée n'est disponible, la card principale affiche un état vide. Le bouton de lancement du radar pointe vers `/radar/` (ID vide) dans ce cas.

### Système de verrouillage

Dans la grille secondaire, une chasse est verrouillée si `rarity.minExperience > user.experience` :

- Carte affichée en style `locked` (couleur grisée).
- Icône de catégorie remplacée par une icône de cadenas.
- Description remplacée par `t("home.levelRequired", { level: reqXp })`.
- Le lien vers `/radar/{huntId}` reste actif malgré le verrou (aucun blocage côté PWA).

### Barre d'XP

Même calcul que dans la page Profil :

```ts
xpProgress = Math.max(0, Math.min(100, (xpGainedInLevel / levelTotalXp) * 100));
```

Si `experienceMax === experienceMin`, la barre reste à 0%.

### Notes

- Le compteur de streak est passé en dur (`{ days: 5 }`) - non connecté à une donnée réelle.
- Les erreurs de chargement sont logées en console uniquement, sans feedback utilisateur.

---

## TreasureHunt (`/treasure-hunts`)

Liste paginée et filtrable de toutes les chasses disponibles.

### Chargement des données

Deux `useEffect` séparés :

**Au montage** (exécuté une seule fois) :

```
GET /api/auth/me  ->  GET /api/users/{id}   (XP et niveau joueur)
GET /api/categories                         (liste des filtres)
```

**À chaque changement de `currentPage` ou `filter`** :

```
GET /api/hunts?page={n}&limit=5[&category={id}]
```

La constante `ITEMS_PER_PAGE` est fixée à `5`.

### Filtrage par catégorie

Les boutons de filtre sont générés depuis la liste des catégories. La valeur `"All"` (type `number | "All"`) désélectionne tout filtre. Changer le filtre remet la pagination à la page 1 et relance le fetch.

La locale est injectée automatiquement par l'intercepteur Axios sur `GET /api/categories`, les noms de catégories sont donc retournés dans la langue courante.

### Pagination

Simple navigation précédent / suivant. Pas de numéros de pages. Affiché uniquement si `totalPages > 1`.

```ts
totalPages = Math.ceil(meta.total / ITEMS_PER_PAGE) || 1
```

Un `window.scrollTo({ top: 0, behavior: "smooth" })` est déclenché à chaque changement de page.

### Affichage des chasses

Chaque chasse est un lien `<Link to="/radar/{id}">`. Pas de système de verrouillage sur cette page - toutes les chasses sont accessibles quelle que soit l'XP du joueur (contrairement à la Home).

Les données affichées par carte : rareté, icône de catégorie (via `getBadgeIcon`), nom de la société, titre, lieu, titre de la récompense.

### Notes

- Les erreurs API sont logées en console uniquement, sans feedback utilisateur.
- Les statistiques joueur (XP, niveau) sont affichées dans le header mais ne bloquent pas l'affichage des chasses si leur chargement échoue.

---

## Reward (`/rewards`)

Inventaire des récompenses obtenues par le joueur.

### Chargement des données

Deux fetches indépendants au montage :

```
GET /api/auth/me         (pseudo joueur pour l'en-tête)
GET /api/me/rewards      (inventaire de récompenses)
```

`isLoading` est contrôlé par le `finally` du fetch des récompenses.

### Calcul du temps restant

La fonction `calculateExpiry(endDate)` calcule le temps restant à l'affichage, en client-side :

| Temps restant | Format affiché |
|---|---|
| Expiré | `t("reward.expired")` |
| > 1 jour | `Xj Yh` |
| < 1 jour | `Xh Ym` |

### Interaction avec une récompense

Clic sur le bouton d'une récompense non expirée :

```ts
window.open(link, "_blank", "noopener,noreferrer")
```

Le bouton est désactivé (`disabled`) si la récompense est expirée (`endDate <= now`), calculé au moment du rendu.

Les récompenses ne sont pas supprimées de l'inventaire au clic. La suppression se fait via `DELETE /api/me/rewards/{id}` (non implémenté dans cette page - voir [endpoints.md](../api/endpoints.md)).

### États de la liste

| État | Affichage |
|---|---|
| Chargement | Texte `t("reward.loading")` |
| Liste non vide | Cards de récompenses |
| Liste vide | Icône + `t("reward.emptyTitle")` + `t("reward.emptyText")` |

### Compteur de récompenses

`rewards.length` est affiché dans l'en-tête avec une icône de ticket. Il correspond au nombre total de récompenses dans l'inventaire, pas seulement celles non expirées.

---

## Service `getBadgeIcon`

Utilisé par Home, TreasureHunt et Profile pour afficher les icônes de catégories et de badges. Mappe une chaîne identifiant (ex : `"fa-medal"`, `"fa-sword"`) vers un composant React (Lucide React ou une map).

```ts
getBadgeIcon(icon: string | undefined, size: number, color?: string): ReactNode
```

Retourne un composant React ou une icône de fallback si l'identifiant n'est pas reconnu.
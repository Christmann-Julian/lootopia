# Authentification - PWA

Ce document décrit le système d'authentification de la PWA joueur. Le service `auth` est quasi-identique à celui de l'admin web - les différences sont signalées explicitement.

---

## Différences avec l'admin web

| Aspect | Admin web | PWA |
|---|---|---|
| Router | React Router 7 (SSR) | React Router DOM 6 (SPA) |
| Routing | `/:lang/...` avec layouts guards | Routes plates `/login`, `/home`... |
| Guards | `clientLoader` dans les layouts | Composants `AuthGuard` / `GuestGuard` |
| Payload JWT | `{ username, roles, iat, exp }` | `{ id, username, roles, iat, exp }` |
| i18n | Multi-namespace | Namespace unique |
| SSR | Oui | Non |

---

## Service auth (`services/auth.ts`)

Le service est identique à celui de l'admin web (même instance Axios, même intercepteur, même pattern singleton sur `isAuth()`). Voir [auth.md de l'admin](../../admin/auth.md) pour la documentation complète.

**Seule différence - payload JWT :**

```ts
type MyTokenPayload = {
  id: number;       // Présent dans la PWA, absent dans l'admin
  username: string;
  roles: string[];
  iat: number;
  exp: number;
};
```

Le champ `id` est présent dans le payload JWT de la PWA mais n'est pas exposé par `getPermissions()` (qui ne retourne que `roles`). La page Profil récupère l'ID via `/api/auth/me` plutôt que depuis le token.

---

## Routing

La PWA utilise un `BrowserRouter` standard sans préfixe de langue. Toutes les routes sont au premier niveau :

```
/                -> Login        (GuestGuard)
/register        -> Register     (GuestGuard)
/home            -> Home         (AuthGuard)
/profile         -> Profile      (AuthGuard)
/rewards         -> Reward       (AuthGuard)
/treasure-hunts  -> TreasureHunt (AuthGuard)
/radar/:huntId   -> Radar        (AuthGuard)
/success         -> Success      (AuthGuard)
*                -> Redirect vers /
```

---

## Guards

Les guards sont des **composants wrapper** (et non des `clientLoader`). Ils vérifient l'authentification dans un `useEffect` et affichent un état de chargement intermédiaire pendant la vérification.

### AuthGuard

Protège les routes nécessitant une authentification.

```
useEffect -> isAuth()
  null (en cours)   ->  <div>Loading...</div>
  false             ->  <Navigate to="/" replace />
  true              ->  {children}
```

### GuestGuard

Protège les routes publiques (login, register) contre les utilisateurs déjà connectés.

```
useEffect -> isAuth()
  null (en cours)   ->  <div>Loading...</div>
  true              ->  <Navigate to="/home" replace />
  false             ->  {children}
```

Les deux guards utilisent le même pattern : `isAuth()` est appelé dans un `useEffect`, le résultat est stocké dans un state local `boolean | null`. Le rendu conditionnel évite tout flash de contenu non autorisé.

---

## Pages d'authentification

### Login (`/`)

- Envoie `POST /api/auth/login` avec `{ email, password }` - pas de `client_type` explicite (l'API applique le comportement `web` par défaut : cookie HttpOnly).
- En cas de succès : `setAccessToken(token)` + navigate vers `/home`.
- Erreur 401 : toast "identifiants invalides".
- Autre erreur : toast "erreur serveur".
- Le formulaire n'est pas réinitialisé après une erreur.

### Register (`/register`)

- Pas de champ `company` - les joueurs n'ont pas de société.
- Erreurs de validation gérées à deux niveaux :
  1. **Toast** avec le message général de l'API.
  2. **Erreurs champ par champ** via `setError` de React Hook Form, depuis le champ `details` de la réponse.
- En cas de succès : toast de succès + `reset()` du formulaire (l'utilisateur reste sur la page).

**Champs et règles :**

| Champ | Règles |
|---|---|
| `firstname` | Requis, min 2, max 100 |
| `lastname` | Requis, min 2, max 100 |
| `pseudo` | Requis |
| `email` | Requis, format email |
| `password` | Requis, min 10, max 255 |
| `confirmPassword` | Doit correspondre à `password` |

---

## Profil (`/profile`)

La page profil regroupe plusieurs fonctionnalités dans un seul composant.

### Chargement des données

Deux appels séquentiels au montage :

```ts
// 1. Récupération de l'ID utilisateur
const meResponse = await api.get("/api/auth/me");
const userId = meResponse.data.id;

// 2. Récupération du profil complet (XP, badges, rank...)
const profileResponse = await api.get(`/api/users/${userId}`);
```

L'ID est récupéré via `/api/auth/me` plutôt que depuis le payload JWT (bien que le champ `id` soit présent dans le token).

En cas d'erreur 401, l'utilisateur est redirigé vers `/login`. Les autres erreurs affichent un toast.

### Formulaire d'informations personnelles

Modifie `firstname`, `lastname`, `pseudo`, `email` via `PUT /api/users/{id}`. Le state `userData` est mis à jour avec la réponse en cas de succès.

### Formulaire de sécurité (mot de passe)

```ts
PUT /api/users/{id}/password
{ currentPassword, newPassword }
```

Le formulaire est réinitialisé (`resetSecurity()`) après succès. En cas d'erreur : toast "identifiants invalides".

**Règle de validation côté client :**
- `newPassword` : requis, min 6 caractères (note : l'API requiert min 10).

### Sélecteur de langue

Un `<select>` dans la section préférences appelle directement `i18n.changeLanguage(value)`. Pas de persistance côté serveur - la préférence est stockée dans i18next (localStorage par défaut selon la configuration).

### Suppression du compte

```ts
window.confirm(t("profile.delete.notice"))
  -> true  ->  DELETE /api/users/{userId}  ->  navigate("/login")
  -> false ->  rien
```

Confirmation native du navigateur. Pas de modale personnalisée.

### Calcul de la barre d'XP

Le pourcentage d'XP dans le rang courant est calculé depuis les bornes du rang :

```ts
const levelTotalXp = experienceMax - experienceMin;
const xpGainedInLevel = currentExperience - experienceMin;
xpPercentage = Math.max(0, Math.min(100, (xpGainedInLevel / levelTotalXp) * 100));
```

Si `experienceMax === experienceMin` (rang mal configuré), le calcul est ignoré et le pourcentage reste à 0.

### Affichage des badges

Les badges sont affichés depuis `userData.badges`. Chaque badge utilise un service `getBadgeIcon(badge.icon, size)` qui mappe l'identifiant d'icône (ex : `"fa-medal"`) vers un composant React. Si aucun badge n'est débloqué, un badge mystère est affiché (`???`).

Les noms de badges et de rangs sont traduits avec la fonction `getTranslatedName` qui cherche dans `item.translations[currentLang]`, puis dans `item.name`, puis retourne le fallback fourni.

```ts
const currentLang = i18n.language?.split("-")[0] || "fr";
const getTranslatedName = (item, fallback) =>
  item?.translations?.[currentLang] || item?.name || fallback;
```
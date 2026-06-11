# Authentification - Admin Web

Ce document décrit le système d'authentification, les guards de routes et la gestion des permissions dans l'interface d'administration.

---

## Vue d'ensemble

L'admin web utilise React Router 7 avec des `clientLoader` et `clientAction` pour les appels API côté client. L'authentification repose sur le JWT retourné par l'API avec `client_type: "web"` : le token est stocké en mémoire (via `setAccessToken`), le refresh token est géré automatiquement par le cookie HttpOnly posé par l'API.

---

## Routing et internationalisation

Toutes les routes sont préfixées par `/:lang` (ex : `/fr/login`, `/en/dashboard`). La langue est lue depuis le premier segment de l'URL et synchronisée avec i18next via le hook `useLanguageSync`.

Le `loader` racine (`root.tsx`) gère les cas suivants :

| Cas | Comportement |
|---|---|
| URL `/` | Aucune redirection (géré par la route index) |
| Langue absente ou vide | Redirect vers `/{fallbackLng}` |
| Langue non supportée | Réponse `404` |

Les langues supportées et la langue par défaut sont définies dans la configuration i18next (`services/i18n`).

**Namespaces i18n utilisés :**

| Namespace | Contenu |
|---|---|
| `auth` | Textes des pages de connexion, inscription, mot de passe |
| `common` | Textes partagés (nom de l'app, erreurs génériques) |
| `validation` | Messages d'erreur des formulaires |
| `form` | Labels et placeholders des formulaires de settings |
| `navigation` | Items de navigation |

---

## Service auth (`services/auth.ts`)

### Instance Axios

```ts
export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL.replace(/^http:/, "https:"),
  withCredentials: true,
});
```

- `VITE_API_URL` est la variable d'environnement qui pointe vers l'API Symfony.
- Le protocole est forcé en HTTPS même si la variable contient `http://`.
- `withCredentials: true` est indispensable pour que le navigateur envoie et reçoive le cookie `REFRESH_TOKEN` sur les requêtes cross-origin.

### Intercepteur de requête

Chaque requête passant par `api` est traitée dans l'ordre suivant :

**1. Attente du refresh en cours**
Si un refresh est déjà en cours (`refreshPromise != null`) et que la requête n'est pas elle-même un refresh, elle attend la résolution avant de continuer. Cela évite des appels API avec un token expiré pendant qu'un nouveau est en cours d'obtention.

**2. Injection du JWT**
Si un token est présent en mémoire, le header `Authorization: Bearer {token}` est ajouté. Les requêtes vers `/api/auth/token/refresh` sont exemptées pour ne pas créer de boucle.

**3. Injection automatique de la locale**
La locale courante d'i18next est automatiquement ajoutée en query string (`?locale=fr`) sur toutes les requêtes qui n'en ont pas déjà une.

Pour désactiver ce comportement sur une requête spécifique, passer le header `X-Skip-Locale: true` - il sera supprimé de la requête avant envoi et la locale ne sera pas ajoutée.

### Stockage du JWT

Le JWT est stocké **en mémoire** dans la variable `accessToken`, jamais dans `localStorage` ou `sessionStorage`. `setAccessToken` met également à jour `api.defaults.headers.common["Authorization"]` pour que toutes les instances bénéficient du token immédiatement.

```ts
setAccessToken(token)   // Stocke en mémoire + met à jour les headers Axios
setAccessToken(null)    // Efface le token + supprime le header Authorization
getAccessToken()        // Retourne le token courant
```

### Structure du payload JWT

```ts
type MyTokenPayload = {
  username: string;   // Email de l'utilisateur
  roles: string[];    // Ex : ["ROLE_USER", "ROLE_ADMIN"]
  iat: number;        // Timestamp d'émission
  exp: number;        // Timestamp d'expiration
};
```

`getPermissions()` décode le JWT avec `jwtDecode` et retourne le tableau `roles`. En cas d'erreur de décodage, retourne `[]`.

### isAuth() - Pattern singleton

```
isAuth() appelé :
  token en mémoire        ->  true immédiatement
  refresh déjà en cours   ->  attend la même Promise (pas de double appel)
  aucun token             ->  démarre un refresh via POST /api/auth/token/refresh
                              { client_type: "web" }  (refresh token lu depuis le cookie)
                              succès  ->  setAccessToken(token), retourne true
                              échec   ->  setAccessToken(null), retourne false
```

Plusieurs `clientLoader` peuvent appeler `isAuth()` simultanément (ex : layouts imbriqués). Le pattern garantit qu'un seul appel réseau est effectué.

---

## Guards de routes (Layouts)

Les guards sont implémentés dans les `clientLoader` des layouts parents. Deux layouts protègent les routes :

### AuthLayout - routes utilisateur

Protège les routes accessibles à tout utilisateur authentifié (`ROLE_USER`).

```
clientLoader :
  isAuth() == false  ->  redirect /{lang}  (page de login)
  ROLE_USER absent   ->  redirect /{lang}/dashboard?error=unauthorized
  OK                 ->  null (accès autorisé)
```

### AdminLayout - routes administrateur

Protège les routes réservées aux administrateurs Lootopia (`ROLE_ADMIN`).

```
clientLoader :
  isAuth() == false  ->  redirect /{lang}  (page de login)
  ROLE_ADMIN absent  ->  redirect /{lang}/dashboard?error=unauthorized
  OK                 ->  null (accès autorisé)
```

Les deux layouts rendent simplement `<Outlet />` sans UI propre.

---

## Hook useCan

Pour les vérifications de permission dans les composants :

```ts
import { useCan } from "../hooks/useCan";

const can = useCan();

if (can("ROLE_ADMIN")) {
  // afficher les actions admin
}
```

`useCan` appelle `getPermissions()` et retourne une fonction qui vérifie si le rôle demandé est présent dans le tableau.

---

## Pages d'authentification

### Login (`/:lang`)

- Envoie `POST /api/auth/login` avec `client_type: "web"`.
- En cas de succès : `setAccessToken(token)` puis redirect vers `/{lang}/dashboard`.
- Le paramètre `?success=...` dans l'URL est lu au montage pour afficher un toast (utilisé après la vérification d'email).
- En cas d'erreur : reset du formulaire + toast d'erreur avec le message retourné par l'API.

### Register (`/:lang/register`)

Formulaire complet avec les validations suivantes :

| Champ | Règles |
|---|---|
| `firstname` | Requis, min 2, max 100 |
| `lastname` | Requis, min 2, max 100 |
| `pseudo` | Requis, min 2, max 100 |
| `email` | Requis, format email |
| `company` | Requis, max 255 |
| `password` | Requis, min 10, max 250 |
| `confirmPassword` | Doit correspondre à `password` |
| `terms` | Requis (checkbox CGU) |

> Le champ `company` est **obligatoire** dans l'admin web. Tous les comptes créés depuis cette interface appartiennent à une société.

En cas de succès : redirect vers `/{lang}/register-success`.

En cas d'erreur API avec `details` : le premier message du premier champ en erreur est affiché en toast.

**Indicateur de force du mot de passe** - évaluation sur 5 critères :

| Critère | Points |
|---|---|
| Longueur >= 10 | +1 |
| Majuscule | +1 |
| Minuscule | +1 |
| Chiffre | +1 |
| Caractère spécial | +1 |

Score 0-2 : `weak` - Score 3-4 : `medium` - Score 5 : `strong`

### Forgot Password (`/:lang/forgot-password`)

- Envoie `POST /api/auth/password/reset/request`.
- Affiche toujours un toast de succès après soumission (même comportement que l'API qui retourne toujours `202`).
- Le formulaire est réinitialisé après soumission.
- En cas d'erreur réseau : toast avec le message d'erreur générique traduit.

### Reset Password (`/:lang/reset-password`)

- Lit `token` et `email` depuis les query params de l'URL (ex : `?token=abc&email=user@example.com`).
- Le token est injecté dans le body au moment du submit, pas stocké dans le formulaire.
- Si le token est absent de l'URL : toast d'erreur immédiat, l'appel API n'est pas effectué.
- Envoie `POST /api/auth/password/reset` avec `{ password, confirmPassword, token }`.
- En cas de succès : toast de succès puis redirect vers `/{lang}` après **2 secondes**.
- Mêmes règles de validation et indicateur de force que le formulaire d'inscription.

### Settings (`/:lang/settings`)

Page de profil utilisateur, accessible depuis le dashboard.

**Chargement (`clientLoader`) :**

```ts
GET /api/auth/me
```

Les données retournées (prénom, nom, pseudo, email, société) pré-remplissent le formulaire via `defaultValues` de React Hook Form.

**Modification (`clientAction`) :**

```ts
PUT /api/users/{id}
```

L'`id` de l'utilisateur est récupéré depuis les données du loader et ajouté au body avant soumission.

**Comportement après succès :** `setAccessToken(null)` puis redirect vers `/{lang}`. L'utilisateur doit se reconnecter. Ce comportement est intentionnel : la modification de l'email invalide le JWT actuel.

La page inclut également le composant `<ChangePassword />` qui gère le changement de mot de passe de façon indépendante.

---

## Gestion des erreurs API

Les `clientAction` suivent le même pattern de traitement des erreurs :

```ts
catch (err) {
  const apiError = (err as AxiosError<ApiErrorResponse>).response?.data;

  // Erreur de validation champ par champ
  if (apiError?.details) {
    const firstError = Object.values(apiError.details)[0];
    return { error: firstError?.[0] || apiError.message };
  }

  // Erreur avec message
  return { error: apiError?.message || "An unexpected error occurred" };
}
```

Côté composant, `{ error: true }` (booléen) déclenche le message d'erreur générique traduit (`common:internalServerError`). Une chaîne de caractères est affichée directement en toast.

---

## Composant Toast

Utilisé sur toutes les pages pour le feedback utilisateur. Accepte les types `success`, `error`, `info`, `warning`. Se ferme via un bouton ou automatiquement selon l'implémentation.

```tsx
<Toast
  message="Operation réussie"
  type="success"
  onClose={() => setToast(null)}
/>
```

---

## Gestion des erreurs de routing

Le `ErrorBoundary` racine (`root.tsx`) intercepte toutes les erreurs non gérées :

- Erreur 404 : redirect vers `/{lang}/not-found`.
- Autres erreurs HTTP : affichage du `statusText`.
- Erreurs JavaScript : affichage du message et de la stack (en développement).

Un composant `<Loading />` est affiché pendant les navigations React Router (détecté via `useNavigation`).
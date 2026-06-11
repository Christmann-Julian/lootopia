# Admin Web

Interface d'administration de Lootopia. Construite avec React 19 et React Router 7 en mode SSR.

> Pour l'installation et le lancement de l'environnement, voir la documentation Docker à la racine du projet.

---

## Concepts clés

### Routing et internationalisation

Toutes les routes sont préfixées par `/:lang` (`/fr/...`, `/en/...`). Les langues supportées sont `fr` et `en`, avec `en` comme fallback. La langue est synchronisée entre l'URL et i18next via le hook `useLanguageSync`. Voir [i18n.md](i18n.md).

### Authentification

Le JWT est stocké en mémoire. Le refresh token est géré via un cookie HttpOnly posé par l'API. Les guards de route sont implémentés dans les `clientLoader` des layouts parents (`AdminLayout`, `AuthLayout`). Voir [auth.md](auth.md).

### Pattern CRUD

Chaque ressource suit le même patron de quatre vues : liste, détail, création, modification. La liste s'appuie sur le composant générique `Table` qui gère lui-même la pagination, le tri, la recherche, la sélection multiple et l'export CSV. Voir [crud.md](crud.md).

---

## Structure du projet

```
front-web/
├── app/
│   ├── components/         # Composants partagés (Table, Show, Toast, Pagination...)
│   ├── hooks/              # useCan, useLanguageSync
│   ├── routes/
│   │   ├── _auth/          # Layout AuthLayout + pages protégées ROLE_USER
│   │   │   ├── dashboard/  # Dashboard + pages CRUD (hunts, users, badges...)
│   │   │   └── settings/
│   │   ├── _admin/         # Layout AdminLayout + pages protégées ROLE_ADMIN
│   │   ├── login/
│   │   ├── register/
│   │   ├── forgot-password/
│   │   └── reset-password/
│   ├── services/
│   │   ├── auth.ts         # Instance Axios, JWT, isAuth(), getPermissions()
│   │   └── i18n.ts         # Configuration i18next
│   └── types/              # Types TypeScript (FormType, ApiType, TableType...)
├── public/
│   └── locales/
│       ├── fr/             # Fichiers de traduction français
│       └── en/             # Fichiers de traduction anglais
└── entry.client.tsx        # Hydratation React + init i18next côté client
    entry.server.tsx        # Rendu SSR + init i18next côté serveur
```

---

## Pages

### Authentification (publiques)

| Route | Description |
|---|---|
| `/:lang` | Connexion |
| `/:lang/register` | Inscription (compte entreprise) |
| `/:lang/forgot-password` | Demande de réinitialisation du mot de passe |
| `/:lang/reset-password` | Saisie du nouveau mot de passe (depuis le lien email) |

### Dashboard et CRUD (ROLE_USER)

| Route | Description |
|---|---|
| `/:lang/dashboard` | Statistiques et graphiques adaptés au rôle |
| `/:lang/dashboard/hunts` | Gestion des chasses |
| `/:lang/dashboard/rewards` | Gestion des récompenses |
| `/:lang/dashboard/users` | Gestion des utilisateurs |
| `/:lang/dashboard/badges` | Gestion des badges |
| `/:lang/dashboard/categories` | Gestion des catégories |
| `/:lang/dashboard/rarities` | Gestion des raretés |
| `/:lang/dashboard/ranks` | Gestion des rangs |
| `/:lang/settings` | Profil et changement de mot de passe |

Chaque ressource expose les sous-routes `create`, `{id}/show`, `{id}/edit`.

---

## Variables d'environnement

| Variable | Description |
|---|---|
| `VITE_API_URL` | URL de base de l'API Symfony (ex : `https://localhost:8000`) |

---

## Sous-documentation

| Fichier | Contenu |
|---|---|
| [auth.md](auth.md) | Service auth, JWT, Axios, guards de routes, pages d'authentification |
| [i18n.md](i18n.md) | Configuration i18next, init client/serveur, synchronisation avec l'URL |
| [crud.md](crud.md) | Composants génériques (Table, Show, Toast, Pagination), patron CRUD |
| [dashboard.md](dashboard.md) | Statistiques, graphiques Recharts, comportement selon le rôle |
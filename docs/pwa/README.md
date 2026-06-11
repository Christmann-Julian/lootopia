# PWA Joueur

Application web progressive destinée aux joueurs de Lootopia. Construite avec React 19 en mode SPA.

> Pour l'installation et le lancement de l'environnement, voir la documentation Docker.

---

## Concepts clés

### Gameplay

Le coeur de l'application est la page `/radar/:huntId` : le joueur se rend physiquement sur les coordonnées d'une chasse, suit sa position en temps réel sur une carte Leaflet, puis active la vue AR quand il entre dans la zone de détection (50 mètres). L'objet 3D (A-Frame + AR.js) apparaît sur le flux caméra - le joueur clique dessus pour déclencher une question, et valide sa réponse pour réclamer la récompense. Voir [game.md](game.md).

### PWA et mode hors-ligne

`vite-plugin-pwa` génère un Service Worker qui met en cache les assets statiques de l'application. L'app peut s'installer sur l'écran d'accueil d'un appareil mobile. Les appels API ne sont pas mis en cache - une connexion réseau est nécessaire pendant le jeu.

### Authentification

JWT en mémoire + cookie HttpOnly pour le refresh token. Les routes protégées sont gardées par les composants `AuthGuard` et `GuestGuard`. Voir [auth.md](auth.md).

### Internationalisation

Deux langues supportées (`fr`, `en`), changement via le profil joueur. Namespace unique, pas de langue dans l'URL. Voir [i18n.md](i18n.md).

---

## Structure du projet

```
lootopia-pwa/
├── src/
│   ├── assets/css/         # Styles par page
│   ├── components/
│   │   ├── ARView.tsx       # Vue AR (A-Frame + AR.js dans iframe)
│   │   ├── LocationTracker.tsx  # Suivi GPS continu (react-leaflet)
│   │   ├── AuthGuard.tsx    # Guard routes protégées
│   │   ├── GuestGuard.tsx   # Guard routes publiques
│   │   ├── Navbar.tsx       # Navigation bottom bar
│   │   └── Toast.tsx        # Notifications
│   ├── pages/
│   │   ├── Login.tsx
│   │   ├── Register.tsx
│   │   ├── Home.tsx         # Accueil + chasses sponsorisées
│   │   ├── TreasureHunt.tsx # Liste paginée des chasses
│   │   ├── Radar.tsx        # Carte GPS + déclencheur AR
│   │   ├── Reward.tsx       # Inventaire des récompenses
│   │   ├── Profile.tsx      # Profil, badges, paramètres
│   │   └── Success.tsx
│   ├── services/
│   │   ├── auth.ts          # Instance Axios, JWT, isAuth()
│   │   ├── i18n.ts          # Configuration i18next
│   │   └── badgeIconService.ts  # Map icône → composant React
│   ├── hooks/
│   │   └── useCan.ts        # Vérification de permission
│   └── types/               # Types TypeScript
├── public/
│   └── locales/
│       ├── fr/translation.json
│       └── en/translation.json
└── App.tsx                  # Routes React Router DOM
```

---

## Routes

| Route | Guard | Description |
|---|---|---|
| `/` | GuestGuard | Connexion |
| `/register` | GuestGuard | Inscription |
| `/home` | AuthGuard | Accueil, chasses sponsorisées, statut joueur |
| `/treasure-hunts` | AuthGuard | Liste paginée des chasses avec filtre catégorie |
| `/radar/:huntId` | AuthGuard | Carte GPS + vue AR + claim de récompense |
| `/rewards` | AuthGuard | Inventaire des récompenses obtenues |
| `/profile` | AuthGuard | Profil, badges, sécurité, langue, suppression |
| `/success` | AuthGuard | Écran de succès |
| `*` | - | Redirect vers `/` |

---

## Variables d'environnement

| Variable | Description |
|---|---|
| `VITE_API_URL` | URL de base de l'API Symfony (ex : `https://localhost:8000`) |

---

## Sous-documentation

| Fichier | Contenu |
|---|---|
| [auth.md](auth.md) | Service auth, guards, login, register, profil joueur |
| [i18n.md](i18n.md) | Configuration i18next, fichiers de traduction, changement de langue |
| [pages.md](pages.md) | Pages Home, TreasureHunt, Reward |
| [game.md](game.md) | Radar, géolocalisation, réalité augmentée, claim de récompense |
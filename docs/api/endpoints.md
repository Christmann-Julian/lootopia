# Endpoints API

Reference complète des routes de l'API Lootopia.
La documentation interactive (Swagger UI) est disponible à l'adresse `/api/doc` sur le serveur local.

---

## Conventions

### Authentification

La majorité des endpoints requiert un token JWT passé dans le header HTTP :

```
Authorization: Bearer <token>
```

Les endpoints publics (non authentifiés) sont signalés par la mention **Public** dans leur description.

### Paramètre locale

Presque tous les endpoints acceptent un paramètre `locale` en query string (`fr` ou `en`).

- **Avec locale** (`?locale=fr`) : le champ `name` retourne la traduction pour la locale demandée.
- **Sans locale** : le champ `name` est `null` et un objet `translations` est retourné à la place.

```json
// Avec ?locale=fr
{ "id": 1, "name": "Médaille d'Or" }

// Sans locale
{ "id": 1, "name": null, "translations": { "fr": "Médaille d'Or", "en": "Gold Medal" } }
```

### Pagination (routes /admin)

Les routes `/admin` retournent une réponse paginée avec la structure suivante :

```json
{
  "data": [...],
  "meta": {
    "page": 1,
    "limit": 10,
    "total": 42,
    "sort": "id",
    "direction": "asc"
  }
}
```

Paramètres communs : `page` (défaut: 1), `limit` (défaut: 10, max: 100), `sort`, `direction` (`asc` / `desc`), `q` (recherche textuelle).

### Rôles

| Rôle | Description |
|---|---|
| `ROLE_USER` | Utilisateur standard ou compte entreprise |
| `ROLE_ADMIN` | Administrateur Lootopia, accès complet |

---

## Reference rapide

| Méthode | Route | Auth | Description |
|---|---|---|---|
| POST | `/api/auth/login` | Public | Connexion, obtention du JWT |
| POST | `/api/auth/token/refresh` | Public | Renouvellement du JWT |
| POST | `/api/auth/logout` | JWT | Déconnexion et révocation du refresh token |
| GET | `/api/auth/me` | JWT | Informations de l'utilisateur courant |
| POST | `/api/auth/register` | Public | Inscription |
| POST | `/api/auth/verify/request` | Public | Renvoi de l'email de vérification |
| GET | `/api/auth/verify` | Public | Vérification de l'email (lien du mail) |
| POST | `/api/auth/password/reset/request` | Public | Demande de réinitialisation du mot de passe |
| POST | `/api/auth/password/reset` | Public | Réinitialisation du mot de passe |
| GET | `/api/badges` | Public | Liste de tous les badges |
| POST | `/api/badges` | Admin | Créer un badge |
| GET | `/api/badges/admin` | Admin | Liste paginée des badges |
| GET | `/api/badges/{id}` | Public | Détail d'un badge |
| PUT | `/api/badges/{id}` | Admin | Modifier un badge |
| DELETE | `/api/badges/{id}` | Admin | Supprimer un badge |
| GET | `/api/categories` | Public | Liste de toutes les catégories |
| POST | `/api/categories` | Admin | Créer une catégorie |
| GET | `/api/categories/admin` | Admin | Liste paginée des catégories |
| GET | `/api/categories/{id}` | Public | Détail d'une catégorie |
| PUT | `/api/categories/{id}` | Admin | Modifier une catégorie |
| DELETE | `/api/categories/{id}` | Admin | Supprimer une catégorie |
| GET | `/api/hunts` | Public | Liste publique des chasses |
| POST | `/api/hunts` | JWT | Créer une chasse (avec récompense) |
| GET | `/api/hunts/admin` | JWT | Liste des chasses (Admin: toutes, User: sa société) |
| GET | `/api/hunts/sponsored` | Public | Liste des chasses sponsorisées |
| GET | `/api/hunts/{id}` | JWT | Détail d'une chasse |
| PUT | `/api/hunts/{id}` | JWT | Modifier une chasse |
| DELETE | `/api/hunts/{id}` | JWT | Supprimer une chasse (cascade récompense) |
| GET | `/api/rarities` | Public | Liste de toutes les raretés |
| POST | `/api/rarities` | Admin | Créer une rareté |
| GET | `/api/rarities/admin` | Admin | Liste paginée des raretés |
| GET | `/api/rarities/{id}` | Public | Détail d'une rareté |
| PUT | `/api/rarities/{id}` | Admin | Modifier une rareté |
| DELETE | `/api/rarities/{id}` | Admin | Supprimer une rareté |
| GET | `/api/ranks` | Public | Liste de tous les rangs |
| POST | `/api/ranks` | Admin | Créer un rang |
| GET | `/api/ranks/admin` | Admin | Liste paginée des rangs |
| GET | `/api/ranks/{id}` | Public | Détail d'un rang |
| PUT | `/api/ranks/{id}` | Admin | Modifier un rang |
| DELETE | `/api/ranks/{id}` | Admin | Supprimer un rang |
| GET | `/api/rewards/admin` | JWT | Liste paginée des récompenses |
| GET | `/api/rewards/{id}` | JWT | Détail d'une récompense |
| PUT | `/api/rewards/{id}` | JWT | Modifier une récompense |
| GET | `/api/statistics/admin` | Admin | Statistiques globales (dashboard admin) |
| GET | `/api/statistics/company` | JWT | Statistiques de la société (dashboard entreprise) |
| GET | `/api/statistics/admin/charts` | Admin | Données graphiques admin |
| GET | `/api/statistics/company/charts` | JWT | Données graphiques entreprise |
| GET | `/api/users/admin` | Admin | Liste paginée des utilisateurs |
| POST | `/api/users` | Admin | Créer un utilisateur |
| GET | `/api/users/{id}` | JWT | Détail d'un utilisateur |
| PUT | `/api/users/{id}` | JWT | Modifier un utilisateur |
| DELETE | `/api/users/{id}` | JWT | Supprimer un utilisateur |
| PUT | `/api/users/{id}/password` | JWT | Modifier le mot de passe |
| POST | `/api/me/hunts/{hunt_id}/participate` | JWT | Enregistrer une participation |
| POST | `/api/me/rewards/{hunt_id}/claim` | JWT | Réclamer la récompense d'une chasse |
| GET | `/api/me/rewards` | JWT | Lister mes récompenses |
| DELETE | `/api/me/rewards/{reward_id}` | JWT | Supprimer une récompense de l'inventaire |

---

## Auth

### POST /api/auth/login

**Public.** Authentifie un utilisateur et retourne un JWT et un refresh token.

**Body**

| Champ | Type | Requis | Description |
|---|---|---|---|
| `email` | string | Oui | |
| `password` | string | Oui | |
| `client_type` | string | Non | `web` ou `pwa` |

**Réponses**

| Code | Description |
|---|---|
| 200 | `{ token, refresh_token }` |
| 401 | Identifiants invalides |
| 403 | Email non vérifié |

---

### POST /api/auth/token/refresh

**Public.** Échange un refresh token contre un nouveau JWT.

**Body**

| Champ | Type | Requis |
|---|---|---|
| `refresh_token` | string | Oui |
| `client_type` | string | Non |

**Réponses**

| Code | Description |
|---|---|
| 200 | `{ token, refresh_token }` |
| 401 | Token expiré ou invalide |
| 403 | Adresse IP différente de celle utilisée lors de la connexion |
| 404 | Utilisateur introuvable |

---

### POST /api/auth/logout

Révoque le refresh token de l'utilisateur.

**Body**

| Champ | Type | Requis |
|---|---|---|
| `refresh_token` | string | Oui |

**Réponses**

| Code | Description |
|---|---|
| 204 | Déconnexion réussie |

---

### GET /api/auth/me

Retourne les informations de l'utilisateur authentifié.

**Réponse 200**

```json
{
  "id": 1,
  "firstname": "Jean",
  "lastname": "Dupont",
  "pseudo": "jdupont",
  "email": "jean.dupont@example.com",
  "company": "Lootopia",
  "roles": ["ROLE_USER"]
}
```

---

### POST /api/auth/register

**Public.** Crée un compte utilisateur. Un email de vérification est envoyé automatiquement.

**Body**

| Champ | Type | Requis |
|---|---|---|
| `firstname` | string | Oui |
| `lastname` | string | Oui |
| `pseudo` | string | Oui |
| `email` | string | Oui |
| `password` | string | Oui |
| `company` | string | Non |

**Réponses**

| Code | Description |
|---|---|
| 201 | Compte créé |
| 400 | Erreur de validation |

---

### POST /api/auth/verify/request

**Public.** Renvoie l'email de vérification si le compte existe et n'est pas encore vérifié.

**Body** : `{ "email": "user@example.com" }`

**Réponses** : `202` (email envoyé si le compte existe) - `400` (compte inexistant ou déjà vérifié)

---

### GET /api/auth/verify

**Public.** Endpoint cible du lien de vérification reçu par email. Redirige vers le frontend avec un paramètre de succès ou d'erreur.

**Query** : `email` (requis)

**Réponse** : `302` (redirection frontend)

---

### POST /api/auth/password/reset/request

**Public.** Envoie un email de réinitialisation si le compte existe.

**Body** : `{ "email": "user@example.com" }`

**Réponse** : `202`

---

### POST /api/auth/password/reset

**Public.** Réinitialise le mot de passe à partir du token reçu par email.

**Body**

| Champ | Type | Requis |
|---|---|---|
| `token` | string | Oui |
| `password` | string | Oui |

**Réponses** : `204` - `400` (token invalide) - `404` (utilisateur introuvable)

---

## Badges

Les badges sont des accomplissements déverrouillables par les joueurs (ex : "10 chasses complétées").

### GET /api/badges

**Public.** Retourne la liste complète des badges.

### POST /api/badges

**Admin.** Crée un badge.

**Body**

```json
{
  "icon": "fa-medal",
  "translations": {
    "fr": "Médaille d'Or",
    "en": "Gold Medal"
  }
}
```

**Réponses** : `201` - `400` - `403`

### GET /api/badges/admin

**Admin.** Liste paginée. Paramètres de tri : `id`, `icon`, `name`.

### GET /api/badges/{id}

**Public.** Détail d'un badge.

**Réponses** : `200` - `404`

### PUT /api/badges/{id}

**Admin.** Modifie l'icône et/ou les traductions d'un badge.

**Réponses** : `200` - `400` - `403` - `404`

### DELETE /api/badges/{id}

**Admin.** Supprime le badge et ses traductions.

**Réponses** : `204` - `403` - `404`

---

## Categories

Les catégories permettent de classer les chasses (ex : aventure, sport, culture...).

### GET /api/categories

**Public.** Liste complète des catégories.

### POST /api/categories

**Admin.** Crée une catégorie.

**Body**

```json
{
  "icon": "fa-sword",
  "translations": { "fr": "Épée", "en": "Sword" }
}
```

### GET /api/categories/admin

**Admin.** Liste paginée. Paramètres de tri : `id`, `icon`, `name`.

### GET /api/categories/{id}

**Public.** Détail d'une catégorie. **Réponses** : `200` - `404`

### PUT /api/categories/{id}

**Admin.** Modifie une catégorie. **Réponses** : `200` - `400` - `403` - `404`

### DELETE /api/categories/{id}

**Admin.** Supprime la catégorie et ses traductions. **Réponses** : `204` - `403` - `404`

---

## Hunts

Les chasses sont le coeur du système. Chaque chasse est liée à une récompense obligatoire, une rareté et une catégorie.

### GET /api/hunts

**Public.** Liste paginée de toutes les chasses. Filtre optionnel par `category` (ID).

**Réponse 200 - structure d'un élément**

```json
{
  "id": 1,
  "lat": 48.8566,
  "lon": 2.3522,
  "isSponsor": false,
  "category": "Aventure",
  "company": "ACME Corp",
  "translations": {
    "title": "Le Trésor Caché",
    "description": "Trouvez le coffre.",
    "question": "Quelle est la couleur du cheval blanc ?",
    "answer": "Blanc",
    "location": "Paris"
  },
  "reward": { ... },
  "rarity": { ... }
}
```

### POST /api/hunts

**JWT.** Crée une chasse et sa récompense en une seule requête. La récompense est obligatoire.

**Body**

```json
{
  "lat": 48.8566,
  "lon": 2.3522,
  "isSponsor": false,
  "categoryId": 1,
  "rarityId": 2,
  "translations": {
    "fr": {
      "title": "Le Trésor Caché",
      "description": "Trouvez le coffre.",
      "question": "Quelle est la couleur du cheval blanc ?",
      "answer": "Blanc",
      "location": "Paris"
    },
    "en": { ... }
  },
  "reward": {
    "code": "PROMO2026",
    "link": "https://example.com/promo",
    "endDate": "2026-12-31T23:59:59Z",
    "translations": { "fr": "Récompense Épique", "en": "Epic Reward" }
  }
}
```

**Réponses** : `201` - `400` - `403`

### GET /api/hunts/admin

**JWT.** Liste paginée avec filtre selon le rôle : un Admin voit toutes les chasses, un User ne voit que celles de sa société. Paramètres de tri : `id`, et autres colonnes disponibles.

### GET /api/hunts/sponsored

**Public.** Retourne les chasses marquées comme sponsorisées. Paramètre `limit` (défaut: 5).

### GET /api/hunts/{id}

**JWT.** Détail complet d'une chasse. **Réponses** : `200` - `403` - `404`

### PUT /api/hunts/{id}

**JWT.** Modifie les champs d'une chasse (hors récompense, à modifier via `/api/rewards/{id}`).

**Réponses** : `200` - `400` - `403` - `404`

### DELETE /api/hunts/{id}

**JWT.** Supprime la chasse et sa récompense en cascade.

**Réponses** : `204` - `403` - `404`

---

## Rarities

Les raretés définissent le niveau d'une récompense et la quantité d'XP gagnée lors du claim.

### GET /api/rarities

**Public.** Liste complète.

**Réponse - structure d'un élément**

```json
{
  "id": 1,
  "minExperience": 500,
  "experienceGain": 50,
  "name": "Légendaire"
}
```

### POST /api/rarities

**Admin.** Crée une rareté.

**Body**

```json
{
  "minExperience": 500,
  "experienceGain": 50,
  "translations": { "fr": "Légendaire", "en": "Legendary" }
}
```

### GET /api/rarities/admin

**Admin.** Liste paginée. Tri par défaut : `minExperience` ascendant.

### GET /api/rarities/{id}

**Public.** **Réponses** : `200` - `404`

### PUT /api/rarities/{id}

**Admin.** **Réponses** : `200` - `400` - `403` - `404`

### DELETE /api/rarities/{id}

**Admin.** Supprime la rareté et ses traductions. **Réponses** : `204` - `403` - `404`

---

## Ranks

Les rangs définissent les niveaux de progression du joueur en fonction de son XP total.

### GET /api/ranks

**Public.** Liste complète.

**Réponse - structure d'un élément**

```json
{
  "id": 1,
  "level": 1,
  "experienceMin": 0,
  "experienceMax": 99,
  "name": "Débutant"
}
```

### POST /api/ranks

**Admin.**

**Body**

```json
{
  "level": 1,
  "experienceMin": 0,
  "experienceMax": 99,
  "translations": { "fr": "Débutant", "en": "Beginner" }
}
```

### GET /api/ranks/admin

**Admin.** Liste paginée. Tri par défaut : `level` ascendant. Colonnes disponibles : `id`, `level`, `experienceMin`, `experienceMax`, `name`.

### GET /api/ranks/{id}

**Public.** **Réponses** : `200` - `404`

### PUT /api/ranks/{id}

**Admin.** **Réponses** : `200` - `400` - `403` - `404`

### DELETE /api/ranks/{id}

**Admin.** **Réponses** : `204` - `403` - `404`

---

## Rewards

Les récompenses sont toujours créées avec leur chasse parente (via `POST /api/hunts`). Cet ensemble d'endpoints permet de les consulter et de les modifier indépendamment.

### GET /api/rewards/admin

**JWT.** Liste paginée. Un Admin voit toutes les récompenses, un User voit uniquement celles liées à sa société.

**Réponse - structure d'un élément**

```json
{
  "id": 1,
  "code": "PROMO2026",
  "link": "https://example.com/promo",
  "endDate": "2026-12-31T23:59:59Z",
  "huntId": 5,
  "title": "Récompense Épique"
}
```

### GET /api/rewards/{id}

**JWT.** Détail d'une récompense. **Réponses** : `200` - `403` - `404`

### PUT /api/rewards/{id}

**JWT.** Modifie le code promo, le lien, la date d'expiration et/ou les traductions.

**Body**

```json
{
  "code": "NEWPROMO",
  "link": "https://example.com/promo2",
  "endDate": "2027-01-01T00:00:00Z",
  "translations": { "fr": "Nouvelle Récompense", "en": "New Reward" }
}
```

**Réponses** : `200` - `400` - `403`

---

## Statistics

### GET /api/statistics/admin

**Admin.** Retourne les trois métriques clés du tableau de bord administrateur.

**Réponse 200**

```json
{
  "totalUsers": 1250,
  "totalHunts": 342,
  "totalCompanies": 45
}
```

### GET /api/statistics/company

**JWT (User avec société).** Retourne les métriques du tableau de bord entreprise.

**Réponse 200**

```json
{
  "totalHuntsCreated": 12,
  "totalUniqueParticipants": 85,
  "totalRewardsClaimed": 120
}
```

### GET /api/statistics/admin/charts

**Admin.** Données de séries temporelles et de distribution pour les graphiques du dashboard admin. Paramètre `locale` (défaut: `fr`).

### GET /api/statistics/company/charts

**JWT (User avec société).** Données de distribution pour les graphiques du dashboard entreprise. Paramètre `locale` (défaut: `fr`).

---

## Users

### GET /api/users/admin

**Admin.** Liste paginée des utilisateurs. Recherche textuelle sur `firstname`, `lastname`, `email`. Colonnes de tri disponibles : `id`, `firstname`, `lastname`, `email`, `company`, `roles`.

**Réponse - structure d'un élément**

```json
{
  "id": 1,
  "firstname": "Jean",
  "lastname": "Dupont",
  "pseudo": "jdupont",
  "email": "jean.dupont@example.com",
  "company": "Lootopia",
  "experience": 100,
  "huntCount": 5,
  "rewardCount": 10,
  "rankLevel": 2,
  "roles": ["ROLE_USER"],
  "isVerified": true
}
```

### POST /api/users

**Admin.** Crée un utilisateur directement (sans email de vérification).

**Body** : mêmes champs que l'inscription + `roles` et `isVerified`.

**Réponses** : `201` - `400` - `403`

### GET /api/users/{id}

**JWT.** Un Admin peut accéder à n'importe quel profil. Un User standard ne peut accéder qu'au sien.

**Réponses** : `200` - `403` - `404`

### PUT /api/users/{id}

**JWT.** Modifie les informations d'un utilisateur. Un Admin peut modifier n'importe quel utilisateur et changer les `roles` et `isVerified`. Un User ne peut modifier que son propre profil.

**Réponses** : `200` - `400` - `403` - `404`

### DELETE /api/users/{id}

**JWT.** Un Admin peut supprimer n'importe quel utilisateur. Un User peut supprimer son propre compte.

**Réponses** : `204` - `403` - `404`

### PUT /api/users/{id}/password

**JWT.** Modifie le mot de passe. Le champ `currentPassword` est requis pour un utilisateur standard (facultatif pour un Admin).

**Body**

```json
{
  "currentPassword": "OldPass123",
  "newPassword": "NewSecret123!"
}
```

**Réponses** : `204` - `400` - `403` - `404`

---

## Player Gameplay

Endpoints dédiés aux actions du joueur en cours de partie.

### POST /api/me/hunts/{hunt_id}/participate

**JWT.** Enregistre la participation du joueur à une chasse. Incrémente le `huntCount` et déclenche la vérification des badges.

**Réponse 200**

```json
{
  "message": "Participation recorded",
  "huntCount": 5
}
```

**Réponses** : `200` - `404`

---

### POST /api/me/rewards/{hunt_id}/claim

**JWT.** Réclame la récompense d'une chasse terminée. Ajoute la récompense à l'inventaire du joueur, octroie l'XP définie par la rareté, recalcule le rang et vérifie les badges débloquables.

**Réponse 200**

```json
{
  "message": "Reward claimed successfully",
  "reward": { ... },
  "userStats": {
    "experience": 1250,
    "huntCount": 6,
    "rewardCount": 3,
    "rank": { ... }
  }
}
```

**Réponses**

| Code | Description |
|---|---|
| 200 | Récompense réclamée |
| 400 | Pas de récompense sur cette chasse, récompense expirée, ou déjà réclamée |
| 404 | Chasse introuvable |

---

### GET /api/me/rewards

**JWT.** Retourne toutes les récompenses présentes dans l'inventaire du joueur authentifié.

**Réponse 200** : `{ "data": [...] }`

---

### DELETE /api/me/rewards/{reward_id}

**JWT.** Retire une récompense de l'inventaire (ex : après utilisation d'un code promo).

**Réponses** : `204` - `404`
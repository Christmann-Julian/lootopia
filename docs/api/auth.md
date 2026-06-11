# Authentification

Ce document décrit le fonctionnement du système d'authentification de l'API Lootopia. Pour la liste des routes et leurs paramètres, voir [endpoints.md](endpoints.md).

---

## Vue d'ensemble

L'API utilise une authentification par **JWT** (JSON Web Token) couplée à un système de **refresh token** personnalisé. Le JWT est de courte durée ; le refresh token, valide 7 jours, permet d'en obtenir un nouveau sans redemander les identifiants.

Deux types de clients sont supportés, avec un comportement différent pour le stockage du refresh token.

---

## Client types

Le champ `client_type` est accepté par les routes `/login` et `/token/refresh`. Il détermine comment le refresh token est transmis.

| `client_type` | Refresh token retourné | Stockage attendu |
|---|---|---|
| `web` (défaut) | Cookie HttpOnly `REFRESH_TOKEN` | Géré automatiquement par le navigateur |
| `mobile` | Corps de la réponse JSON (`refresh_token`) | Stocké côté client (ex : SecureStorage) |

**Cookie web** (configuration) :
- Nom : `REFRESH_TOKEN`
- `HttpOnly: true` - inaccessible depuis JavaScript
- `Secure: true` - HTTPS uniquement
- `SameSite: Lax`
- Durée : 7 jours

Pour les clients `web`, le refresh token ne doit pas être passé dans le body de `/token/refresh` : il est lu automatiquement depuis le cookie s'il est absent du body.

---

## Cycle de vie des tokens

### Connexion

```
Client                          API
  |                              |
  |-- POST /api/auth/login ----->|
  |   { email, password,         |
  |     client_type }            |
  |                              |-- Vérifie identifiants
  |                              |-- Vérifie isVerified
  |                              |-- Génère JWT
  |                              |-- Crée RefreshToken en BDD
  |                              |   (ip, userAgent, expiresAt +7j)
  |<-- 200 { token } + Cookie ---|   (web)
  |<-- 200 { token,              |   (mobile)
  |          refresh_token } ----|
```

### Rafraichissement du JWT

Le refresh token est validé sur trois critères :
- non expiré (TTL 7 jours)
- non révoqué
- **adresse IP identique** à celle utilisée lors de la création - retourne `403` si différente

En cas de succès, l'ancien refresh token est révoqué et un nouveau est créé (rotation).

```
Client                          API
  |                              |
  |-- POST /api/auth/            |
  |   token/refresh ------------>|
  |   { client_type }            |  (web: refresh token lu depuis cookie)
  |   { refresh_token, ... }     |  (mobile: refresh token dans le body)
  |                              |-- Vérifie token (expiré, révoqué, IP)
  |                              |-- Révoque l'ancien refresh token
  |                              |-- Crée un nouveau refresh token
  |<-- 200 { token } + Cookie ---|
```

### Déconnexion

La déconnexion révoque **tous** les refresh tokens de l'utilisateur, pas uniquement celui fourni. Cela invalide toutes les sessions actives sur tous les appareils.

Le cookie `REFRESH_TOKEN` est effacé côté serveur dans la réponse.

---

## Inscription

### Flux complet

1. `POST /api/auth/register` avec les données utilisateur.
2. L'API crée un `User` (non vérifié, `isVerified: false`) et, si un nom de société est fourni, une entité `Company` liée.
3. Le rang de niveau 1 est assigné automatiquement. L'XP, `huntCount` et `rewardCount` sont initialisés à `0`.
4. Un email de vérification est envoyé (lien signé, valable un temps limité).
5. La réponse `201` ne contient que l'email de confirmation - **aucun token n'est retourné** à l'inscription.

Le compte est bloqué à la connexion (`403`) tant que l'email n'est pas vérifié.

### Valeurs initiales à la création

| Champ | Valeur |
|---|---|
| `experience` | 0 |
| `huntCount` | 0 |
| `rewardCount` | 0 |
| `rank` | Rang de niveau 1 |
| `isVerified` | `false` |
| `roles` | `["ROLE_USER"]` |

---

## Vérification de l'email

Le système utilise `symfonycasts/verify-email-bundle` pour générer et valider des URLs signées.

### Flux

1. L'utilisateur reçoit un email contenant un lien signé pointant vers `GET /api/auth/verify`.
2. L'API valide la signature et passe `isVerified` à `true`.
3. L'API redirige vers le frontend avec un paramètre de résultat :
   - Succès : `{FRONTEND_URL}/?success=...`
   - Echec : `{FRONTEND_URL}/?error=...`

Le frontend doit lire ces query params pour afficher le message approprié.

Il est possible de renvoyer l'email de vérification via `POST /api/auth/verify/request`. La route retourne toujours `202` si l'email est valide, même si aucun email n'est envoyé, pour ne pas exposer l'existence du compte.

---

## Réinitialisation du mot de passe

### Flux

1. `POST /api/auth/password/reset/request` - L'API génère un token aléatoire (64 caractères hex) valable **15 minutes**, le stocke en BDD avec l'IP et le user-agent, et envoie un email contenant un lien vers le frontend.

   Format du lien : `{FRONTEND_URL}/{locale}/reset-password?token={token}&email={email}`

2. Le frontend redirige l'utilisateur vers son formulaire de saisie du nouveau mot de passe et transmet le token reçu.

3. `POST /api/auth/password/reset` avec `{ token, password }` - L'API vérifie le token (existence + expiration), met à jour le mot de passe hashé.

La route de demande retourne toujours `202` qu'un compte existe ou non, pour ne pas exposer les emails inscrits.

---

## Format des erreurs

Toutes les erreurs de l'API suivent la structure suivante :

```json
{
  "code": 401,
  "message": "Invalid credentials",
  "details": []
}
```

Le champ `details` contient des erreurs de validation champ par champ quand applicable :

```json
{
  "code": 400,
  "message": "Validation error",
  "details": [
    { "field": "email", "message": "This value is not a valid email address." },
    { "field": "password", "message": "This value is too short. It should have 8 characters or more." }
  ]
}
```

En environnement `prod`, les erreurs `500` retournent `"Internal Server Error"` sans exposer le message d'exception.

---

## Entités

### User

| Champ | Type | Description |
|---|---|---|
| `id` | int | Identifiant |
| `email` | string (unique) | Identifiant de connexion |
| `pseudo` | string (unique) | Nom affiché |
| `firstname` | string | |
| `lastname` | string | |
| `roles` | string[] | Toujours au moins `ROLE_USER` |
| `isVerified` | bool | Email confirmé |
| `experience` | int | XP total accumulé |
| `huntCount` | int | Nombre de chasses participées |
| `rewardCount` | int | Nombre de récompenses obtenues |
| `rank` | Rank (nullable) | Rang courant, `SET NULL` si le rang est supprimé |
| `badges` | Badge[] | Badges débloqués |
| `company` | Company (nullable) | Société liée (OneToOne) |
| `rewards` | Reward[] | Inventaire de récompenses |
| `createdAt` | datetime | |
| `updatedAt` | datetime | |

### RefreshToken

| Champ | Type | Description |
|---|---|---|
| `token` | string (unique) | UUID v4 |
| `userIdentifier` | string | Email de l'utilisateur |
| `revoked` | bool | Révoqué manuellement |
| `ipAddress` | string | IP à la création |
| `userAgent` | string | User-Agent à la création |
| `expiresAt` | datetime | Date d'expiration (TTL +7 jours) |
| `createdAt` | datetime | |

### PasswordResetToken

| Champ | Type | Description |
|---|---|---|
| `token` | string (unique) | 64 caractères hex (`bin2hex(random_bytes(32))`) |
| `user` | User | `CASCADE DELETE` si l'utilisateur est supprimé |
| `ipAddress` | string | IP de la demande |
| `userAgent` | string | User-Agent de la demande |
| `expiresAt` | datetime | +15 minutes après création |
| `createdAt` | datetime | |
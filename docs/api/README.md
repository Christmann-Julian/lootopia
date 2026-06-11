# API Symfony

API REST du projet Lootopia. Construit avec Symfony 7.3 et PHP 8.2+.

> Pour l'installation et le lancement de l'environnement, voir la documentation Docker à la racine du projet.

---

## Documentation interactive

Une interface Swagger UI est disponible en environnement local :

```
https://localhost:8000/api/doc
```

Elle liste tous les endpoints avec leurs paramètres, corps de requête et réponses, générée automatiquement depuis les attributs `#[OA\...]` dans les contrôleurs via NelmioApiDoc.

---

## Concepts clés

### Authentification

L'API utilise JWT (courte durée) + refresh token (7 jours). Deux modes sont supportés selon le `client_type` : `web` (cookie HttpOnly) et `mobile` (body JSON). Voir [auth.md](auth.md).

### Paramètre locale

Presque tous les endpoints acceptent `?locale=fr` ou `?locale=en` en query string. Sans ce paramètre, les entités traduites retournent un objet `translations` complet. Avec, elles retournent le champ `name` (ou `title`) directement pour la locale demandée.

### Pagination

Les routes `/admin` retournent systématiquement `{ data: [...], meta: { page, limit, total, sort, direction } }`. Les paramètres communs sont `page`, `limit` (min 5, max 100), `sort`, `direction`, `q`.

### Gestion des erreurs

Toutes les erreurs passent par `ApiException` et sont formatées par `ApiExceptionSubscriber` :

```json
{
  "code": 400,
  "message": "Validation error",
  "details": [{ "field": "email", "message": "This value is not a valid email address." }]
}
```

En `APP_ENV=prod`, les erreurs 500 retournent `"Internal Server Error"` sans exposer la trace.

---

## Structure du projet

```
symfony-api/
├── src/
│   ├── Controller/         # Un fichier par ressource (AuthController, HuntController...)
│   ├── Entity/             # Entités Doctrine (User, Hunt, Reward, Badge, Rank, Rarity...)
│   ├── Repository/         # Repositories avec requêtes personnalisées
│   ├── Dto/                # Objets de transfert pour la validation des entrées
│   ├── Validator/          # DtoValidator (délègue au Validator Symfony)
│   ├── Service/            # Logique métier (PlayerProgressService...)
│   ├── Security/           # RefreshTokenManager, EmailVerifier
│   ├── Exception/          # ApiException
│   ├── EventListener/      # LocaleListener
│   └── EventSubscriber/    # ApiExceptionSubscriber
├── tests/
│   ├── Functional/         # Tests HTTP bout-en-bout (WebTestCase)
│   └── Unit/               # Tests unitaires avec mocks (TestCase)
├── config/
├── migrations/
└── templates/emails/       # Templates Twig pour les emails transactionnels
```

---

## Variables d'environnement

| Variable | Description |
|---|---|
| `APP_ENV` | Environnement : `dev`, `prod`, `test` |
| `APP_SECRET` | Clé secrète Symfony |
| `DATABASE_URL` | Connexion MySQL (`mysql://user:pass@host:3306/db`) |
| `JWT_SECRET_KEY` | Chemin vers la clé privée JWT |
| `JWT_PUBLIC_KEY` | Chemin vers la clé publique JWT |
| `JWT_PASSPHRASE` | Passphrase de la clé JWT |
| `FRONTEND_URL` | URL du frontend (ex : `http://localhost:5173`) - utilisée pour les redirections email |
| `MAILER_DSN` | Transport email (ex : `smtp://localhost:1025`) |

---

## Sous-documentation

| Fichier | Contenu |
|---|---|
| [auth.md](auth.md) | Système JWT, refresh token, inscription, vérification email, reset mot de passe |
| [endpoints.md](endpoints.md) | Reference complète de toutes les routes |
| [hunt.md](hunt.md) | Modèle Hunt/Reward, règles d'accès, gameplay, progression du joueur |
| [test.md](test.md) | Tests PHPUnit, PHPStan, PHP-CS-Fixer |
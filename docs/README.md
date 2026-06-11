# Lootopia

Plateforme de chasses au trésor numériques mêlant géolocalisation et réalité augmentée.

---

## Présentation

Lootopia permet à des **marques** de créer des chasses au trésor géolocalisées pour récompenser leurs utilisateurs (réductions, bons d'achat, cadeaux, etc.).

**Déroulement d'une chasse :**

1. Le joueur se rend dans une **zone géographique** définie par la marque.
2. Il active sa caméra et cherche une **anomalie en réalité augmentée** (un objet 3D placé dans l'espace réel).
3. Il **clique sur l'objet** pour déclencher une question ou un défi.
4. S'il répond correctement, il **remporte la récompense** associée.

**Système de gamification :**

- **XP & niveaux** - chaque participation fait progresser le joueur.
- **Rareté des récompenses** - niveaux de rareté (commun, rare, épique...).
- **Badges** - déverrouillables selon les accomplissements (ex : *10 chasses complétées*).

---

## Architecture

Lootopia est composé de trois applications distinctes qui communiquent via une API REST centrale.

```
+-----------------------+        +-----------------------+
|      Admin Web        |        |          PWA          |
|    (React / Vite)     |        |     (React / Vite)    |
|  Gestion des chasses  |        |   Interface joueur    |
|  stats, marques...    |        |   géoloc, AR, profil  |
+----------+------------+        +------------+----------+
           |                                  |
           |          HTTPS / JWT             |
           +------------------+---------------+
                              |
                 +------------+------------+
                 |       API Symfony       |
                 |        REST / JSON      |
                 |    Auth, Métier, BDD    |
                 +------------+------------+
                              |
                       +------+------+
                       |    MySQL    |
                       +-------------+
```

---

## Stack technique

### API - `symfony-api`

| Catégorie         | Technologie                                  |
|-------------------|----------------------------------------------|
| Langage           | PHP >= 8.2                                   |
| Framework         | Symfony 7.3                                  |
| Base de données   | MySQL + Doctrine ORM 3                       |
| Authentification  | JWT - `lexik/jwt-authentication-bundle`      |
| Documentation API | NelmioApiDoc (OpenAPI)                       |
| Emails            | Symfony Mailer + `symfonycasts/verify-email` |
| Messaging         | Symfony Messenger                            |
| Pagination        | KnpPaginatorBundle                           |
| Qualité de code   | PHPStan, PHP-CS-Fixer                        |
| Tests             | PHPUnit 11, Doctrine Fixtures, Faker         |

### Admin Web - `front-web`

| Catégorie            | Technologie             |
|----------------------|-------------------------|
| Framework            | React 19 + TypeScript   |
| Bundler              | Vite 6                  |
| Routing              | React Router 7          |
| Requêtes HTTP        | Axios                   |
| Formulaires          | React Hook Form         |
| Graphiques           | Recharts                |
| Internationalisation | i18next + react-i18next |
| Tests                | Vitest + Testing Library|
| Qualité de code      | ESLint, Prettier        |

### PWA - `lootopia-pwa`

| Catégorie            | Technologie                            |
|----------------------|----------------------------------------|
| Framework            | React 19 + TypeScript                  |
| Bundler              | Vite 8                                 |
| PWA                  | vite-plugin-pwa (Service Worker)       |
| Routing              | React Router 7                         |
| Requêtes HTTP        | Axios                                  |
| Formulaires          | React Hook Form                        |
| Cartographie         | Leaflet + React-Leaflet                |
| Réalité augmentée    | *(voir [`docs/pwa/ar.md`](pwa/ar.md))* |
| Internationalisation | i18next + react-i18next                |
| Qualité de code      | ESLint, Prettier                       |

---

## Structure du dépôt

```
lootopia/
├── .github/            # CI github
├── api/                # API
├── front-web/          # Interface d'administration pour les marques et les admins
├── front-pwa/          # PWA pour participer aux chasses
├── docker/             # Docker d'installation du projet
└── docs/               # Documentation du projet (vous êtes ici)
    ├── README.md
    ├── architecture.md
    ├── api/
    ├── admin/
    └── pwa/
```

---

## Documentation

| Section                         | Description                                     |
|---------------------------------|-------------------------------------------------|
| [Architecture](architecture.md) | Diagrammes et flux de données détaillés         |
| [API](api/README.md)            | Endpoints, authentification, entités            |
| [Admin](admin/README.md)        | Interface d'administration, fonctionnalités     |
| [PWA](pwa/README.md)            | Application joueur, géoloc, AR, récompenses     |
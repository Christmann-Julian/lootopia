# Tests et qualité de code

Ce document couvre la stratégie de test, l'organisation des fichiers, les commandes disponibles, ainsi que les outils d'analyse statique et de style.

---

## Outils

| Outil | Version | Rôle |
|---|---|---|
| PHPUnit | 11.5 | Tests unitaires et fonctionnels |
| `dama/doctrine-test-bundle` | 8.5 | Isolation de la BDD par transaction |
| Doctrine Fixtures + Faker | - | Jeux de données de test |
| PHPStan | 2.1 | Analyse statique |
| PHP-CS-Fixer | 3.89 | Style de code |

---

## Structure des tests

```
tests/
├── Functional/
└── Unit/
```

Les tests **fonctionnels** (`Functional/`) lancent un vrai kernel Symfony avec une base de données de test et vérifient les réponses HTTP de bout en bout.

Les tests **unitaires** (`Unit/`) isolent chaque classe avec des mocks PHPUnit et ne requièrent pas de base de données.

---

## Lancer les tests

```bash
# Tous les tests
php bin/phpunit

# Un fichier spécifique
php bin/phpunit tests/Functional/Controller/AuthControllerTest.php

# Un test précis
php bin/phpunit --filter testLoginSuccess

# Avec couverture de code (nécessite Xdebug ou PCOV)
php bin/phpunit --coverage-html coverage/
```

---

## Tests fonctionnels

### Isolation de la base de données

Le bundle `dama/doctrine-test-bundle` enveloppe chaque test dans une transaction qui est annulée à la fin. La base de données revient à son état initial après chaque test sans avoir à la recréer. Aucune configuration supplémentaire n'est nécessaire.

### FixtureAwareTrait

Le trait `FixtureAwareTrait` fournit les méthodes suivantes pour charger des fixtures dans les tests fonctionnels :

```php
$this->addFixture(UserFixtures::class);  // Enregistre une fixture
$this->executeFixtures();                 // Exécute toutes les fixtures enregistrées

$this->getRepository(User::class);        // Accès à un repository Doctrine
$this->getEntityManager();                // Accès à l'EntityManager
```

### UserFixtures

La fixture `UserFixtures` crée un compte administrateur utilisé dans la majorité des tests fonctionnels :

| Champ | Valeur |
|---|---|
| Email | `admin@lootopia.fr` |
| Mot de passe | `admin` |
| Rôle | `ROLE_ADMIN` |
| `isVerified` | `true` |

### Effectuer une requête HTTP

```php
$this->client->request(
    'POST',
    '/api/auth/login',
    [],                                          // query params
    [],                                          // fichiers
    ['CONTENT_TYPE' => 'application/json'],      // server vars / headers
    (string) json_encode([...])                  // body
);

$this->assertResponseStatusCodeSame(Response::HTTP_OK);
$data = json_decode((string) $this->client->getResponse()->getContent(), true);
```

**Header d'authentification JWT :**

```php
$this->client->request(
    'GET',
    '/api/auth/me',
    [],
    [],
    ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
);
```

**Simuler une adresse IP :**

```php
$request = new Request(
    [], [], [], [], [],
    ['REMOTE_ADDR' => '192.168.1.50'],
    (string) json_encode([...])
);
```

### Exemples de tests fonctionnels (`AuthControllerTest`)

| Test | Ce qui est vérifié |
|---|---|
| `testLoginSuccess` | Réponse 200 avec `token` et `refresh_token` (client mobile) |
| `testLoginFailureInvalidCredentials` | Réponse 401 sur mauvais mot de passe |
| `testLoginUnverifiedUser` | Réponse 403 si `isVerified = false` |
| `testMe` | Réponse 200 avec l'email de l'utilisateur connecté |
| `testMeUnauthorized` | Réponse 401 sans token |
| `testRegister` | Réponse 201, utilisateur créé en BDD, `isVerified = false` |
| `testRefreshToken` | Nouveau JWT différent de l'ancien après refresh |
| `testLogout` | Refresh token révoqué : le refresh suivant retourne 401 |
| `testRequestPasswordReset` | Réponse 202, `PasswordResetToken` créé en BDD |
| `testResetPassword` | Réponse 204, connexion possible avec le nouveau mot de passe |

---

## Tests unitaires

### Principe

Les tests unitaires utilisent `PHPUnit\Framework\TestCase` et des mocks pour isoler la classe testée de toutes ses dépendances.

```php
$this->userRepository = $this->createMock(UserRepository::class);

$this->userRepository->expects($this->once())
    ->method('findOneBy')
    ->with(['email' => $email])
    ->willReturn($user);
```

### Instanciation manuelle du contrôleur

Les contrôleurs sont instanciés directement avec leurs dépendances mockées plutôt que via le conteneur Symfony :

```php
$this->controller = new AuthController('http://localhost:3000');

$response = $this->controller->login(
    $request,
    $this->userRepository,
    $this->passwordHasher,
    $this->jwtManager,
    $this->refreshTokenManager,
    $this->translator
);
```

### Exemples de tests unitaires (`AuthControllerTest`)

| Test | Ce qui est vérifié |
|---|---|
| `testLoginSuccess` | Le JWT est généré et `createResponseWithJwt` est appelé |
| `testLoginInvalidCredentials` | `ApiException` 401 levée si mot de passe invalide |
| `testLoginUnverifiedUser` | `ApiException` 403 levée si `isVerified = false` |
| `testRegisterSuccess` | `persist` appelé deux fois (User + Company), `flush` une fois, email envoyé |
| `testRefreshSuccess` | Ancien token révoqué, nouveau JWT créé, réponse retournée |
| `testRefreshIpMismatch` | `ApiException` 403 avec message "IP address mismatch" si IP différente |

---

## PHPStan - Analyse statique

PHPStan détecte les erreurs de type, les appels de méthodes inexistantes, les variables indéfinies et d'autres problèmes sans exécuter le code.

```bash
composer phpstan
# ou directement :
./vendor/bin/phpstan analyse
```

La configuration se trouve dans `phpstan.neon` (ou `phpstan.dist.neon`) à la racine du projet. Le niveau d'analyse actuel est défini dans ce fichier (0 = minimal, 9 = maximal).

**Erreurs courantes signalées par PHPStan :**

- Appels sur des valeurs potentiellement `null` sans vérification.
- Paramètres de type incorrect passés à une méthode.
- Propriétés non initialisées dans le constructeur.
- Retours de méthode incompatibles avec la signature déclarée.

PHPStan s'intègre aux attributs `#[ORM\...]` et aux annotations via `phpstan/phpdoc-parser`, ce qui lui permet d'analyser les relations Doctrine correctement.

---

## PHP-CS-Fixer - Style de code

PHP-CS-Fixer applique automatiquement les règles de style définies dans `.php-cs-fixer.dist.php`.

```bash
# Applique les corrections
composer fix
# ou directement :
./vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php

# Vérifie sans modifier (mode dry-run, utile en CI)
./vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff
```

**Ce que PHP-CS-Fixer enforce typiquement :**

- Indentation et espacement cohérents.
- Ordre des `use` (alphabétique, groupé par type).
- Suppression des `use` inutilisés.
- Accolades et retours à la ligne selon PSR-12.
- Opérateurs de casting, virgules finales, etc.

La configuration exacte des règles appliquées est dans `.php-cs-fixer.dist.php` à la racine du projet.

---

## Intégration en CI

Pour un pipeline de qualité, les 3 sont dans la ci github.

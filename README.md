# Lootopia - projet d'étude M1

## Présentation du projet

Lootopia est une plateforme de chasse aux trésors complète composée de plusieurs services :

- **API Symfony** : Fournit les fonctionnalités backend nécessaires.
- **Front Web** : Une application web développée avec React et Vite.
- **Front Mobile** : Une application mobile basée sur Expo et React Native.

Pour plus de détails sur le lancement du projet et la configuration Docker, consultez le [README Docker](docker/README.md).

---

## Documentation

La documentation technique du projet est disponible dans le dossier [`docs/`](docs/README.md).

| Section | Description |
|---|---|
| [API Symfony](docs/api/README.md) | Endpoints, authentification, entités, tests |
| [Admin Web](docs/admin/README.md) | Interface d'administration, CRUD, dashboard |
| [PWA Joueur](docs/pwa/README.md) | Application joueur, géolocalisation, réalité augmentée |

---

## Convention de nommage des commits

Nous utilisons la convention suivante pour les messages de commit :

```
<type>(<scope>): <description>
```

### Types courants :

- **feat** : Ajout d'une nouvelle fonctionnalité.
- **fix** : Correction de bug.
- **docs** : Modifications de documentation.
- **style** : Changements de style (formatage, espaces, etc.).
- **refactor** : Refactorisation du code sans ajout de fonctionnalité ni correction de bug.
- **test** : Ajout ou modification de tests.
- **chore** : Changements mineurs (mise à jour de dépendances, etc.).

### Exemple :

```
feat(api): ajout de la fonctionnalité de connexion
```

---

## Contributeurs

**Christmann Julian**
- <https://github.com/Christmann-Julian>

**Baloup Clément**
- <https://github.com/Itaazz>

**Mohammad Bilal**
- <https://github.com/bilal2709>
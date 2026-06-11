# Hunts et gameplay

Ce document décrit le modèle de données des chasses, les règles d'accès, et la logique de progression du joueur. Pour la liste des routes et leurs paramètres, voir [endpoints.md](endpoints.md).

---

## Entités

### Hunt

| Champ | Type | Contrainte | Description |
|---|---|---|---|
| `id` | int | PK | |
| `lat` | float | NOT NULL | Latitude du point AR |
| `lon` | float | NOT NULL | Longitude du point AR |
| `isSponsor` | bool | défaut `false` | Chasse sponsorisée |
| `company` | Company | ManyToOne, nullable | Société propriétaire |
| `category` | Category | ManyToOne, nullable | Catégorie |
| `rarity` | Rarity | ManyToOne, NOT NULL | Rareté (obligatoire) |
| `reward` | Reward | OneToOne, cascade persist+remove | Récompense liée |
| `huntTranslations` | HuntTranslation[] | OneToMany, orphanRemoval, cascade persist | Traductions |

La suppression d'une chasse entraîne la suppression en cascade de sa `Reward` (et des `RewardTranslation` associées).

### HuntTranslation

| Champ | Type | Description |
|---|---|---|
| `locale` | string (5) | Ex : `fr`, `en` |
| `title` | string (255) | Titre de la chasse |
| `description` | text | Description longue |
| `question` | string (255) | Question posée au joueur |
| `answer` | string (255) | Réponse attendue |
| `location` | string (255) | Nom du lieu (texte libre) |

### Reward

| Champ | Type | Contrainte | Description |
|---|---|---|---|
| `id` | int | PK | |
| `code` | string (255) | NOT NULL | Code promo ou identifiant |
| `link` | string (255) | NOT NULL | URL de la récompense |
| `endDate` | DateTime | NOT NULL | Date d'expiration |
| `hunt` | Hunt | OneToOne, NOT NULL | Chasse parente |
| `rewardTranslations` | RewardTranslation[] | OneToMany, orphanRemoval, cascade persist | Traductions |

### RewardTranslation

| Champ | Type | Description |
|---|---|---|
| `locale` | string (5) | Ex : `fr`, `en` |
| `title` | string (255) | Nom de la récompense |

---

## Format de réponse

Le comportement de `toArray()` varie selon la présence du paramètre `locale`.

**Avec `?locale=fr`** - les champs traduits sont retournés à plat :

```json
{
  "id": 1,
  "lat": 48.8566,
  "lon": 2.3522,
  "isSponsor": false,
  "company": "ACME Corp",
  "title": "Le Trésor Caché",
  "description": "Trouvez le coffre.",
  "question": "Quelle est la couleur du cheval blanc ?",
  "answer": "Blanc",
  "location": "Paris",
  "category": { "id": 1, "name": "Aventure" },
  "rarity": { "id": 2, "name": "Épique", "experienceGain": 50 },
  "reward": {
    "id": 5,
    "code": "PROMO2026",
    "link": "https://example.com/promo",
    "endDate": "2026-12-31T23:59:59+00:00",
    "title": "Récompense Épique"
  }
}
```

**Sans `locale`** - les traductions sont regroupées dans un objet `translations` :

```json
{
  "id": 1,
  "lat": 48.8566,
  "lon": 2.3522,
  "isSponsor": false,
  "company": "ACME Corp",
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
  "category": { "id": 1, "translations": { "fr": "Aventure", "en": "Adventure" } },
  "rarity": { ... },
  "reward": {
    "id": 5,
    "code": "PROMO2026",
    "translations": { "fr": "Récompense Épique", "en": "Epic Reward" }
  }
}
```

---

## Règles d'accès

### Qui peut gérer une chasse ?

La méthode `canManageHunt()` détermine si un utilisateur a le droit de modifier ou supprimer une chasse :

- un Admin (`ROLE_ADMIN`) peut gérer n'importe quelle chasse.
- un User peut uniquement gérer les chasses appartenant à **sa propre société** (comparaison d'objet `Company`).

Un utilisateur sans société associée ne peut **pas créer de chasse** (`400` - "User must belong to a company to create a hunt").

### Restriction sur `isSponsor`

Le champ `isSponsor` ne peut être positionné à `true` que par un Admin.

| Rôle | Création | Modification |
|---|---|---|
| Admin | Valeur du body respectée | Valeur du body respectée si présente |
| User | Forcé à `false` | Valeur existante conservée |

### Route publique vs authentifiée

Malgré son nom, la route `GET /api/hunts` requiert `ROLE_USER`. Il n'existe pas de route réellement publique pour les chasses.

---

## Création d'une chasse

La création d'une chasse et de sa récompense est atomique : les deux sont persistés en une seule requête et un seul appel à `flush()`.

**Règles de validation :**
- `rarityId` est obligatoire et doit correspondre à une rareté existante.
- `categoryId` est optionnel, mais s'il est fourni, la catégorie doit exister.
- Les traductions sont fournies par locale dans `translations`, chaque locale génère une entité `HuntTranslation`.
- Les traductions de la récompense (`reward.translations`) sont de simples clés `locale → titre`.

**Exemple de body minimal :**

```json
{
  "lat": 48.8566,
  "lon": 2.3522,
  "rarityId": 2,
  "translations": {
    "fr": {
      "title": "Le Trésor Caché",
      "description": "Trouvez le coffre.",
      "question": "Quelle est la couleur du cheval blanc ?",
      "answer": "Blanc",
      "location": "Paris"
    }
  },
  "reward": {
    "code": "PROMO2026",
    "link": "https://example.com/promo",
    "endDate": "2026-12-31T23:59:59Z",
    "translations": {
      "fr": "Récompense Épique"
    }
  }
}
```

---

## Modification d'une chasse

La modification est partielle : seuls les champs présents dans le body sont traités. Les valeurs manquantes reprennent les valeurs actuelles de l'entité.

**Comportement sur les traductions :**
- Si une locale fournie correspond à une `HuntTranslation` existante, elle est mise à jour.
- Si la locale n'existe pas encore, une nouvelle `HuntTranslation` est créée.
- Les traductions absentes du body ne sont **pas supprimées**.

La récompense ne se modifie pas via `PUT /api/hunts/{id}`, il faut utiliser `PUT /api/rewards/{id}`.

---

## Gameplay joueur

### Participer à une chasse

`POST /api/me/hunts/{hunt_id}/participate`

Enregistre la participation du joueur :

1. Incrémente `user.huntCount` de 1.
2. Vérifie et attribue les badges débloqués.
3. Retourne le nouveau `huntCount`.

Aucune vérification de doublon n'est effectuée : un joueur peut appeler cet endpoint plusieurs fois sur la même chasse.

### Réclamer une récompense

`POST /api/me/rewards/{hunt_id}/claim`

Conditions vérifiées avant attribution (retournent `400` si non satisfaites) :

| Condition | Message |
|---|---|
| La chasse n'a pas de récompense | "This hunt has no reward" |
| `reward.endDate` < maintenant | "This reward has expired" |
| Le joueur possède déjà cette récompense | "You have already claimed this reward" |

Si toutes les conditions sont satisfaites, dans l'ordre :

1. La récompense est ajoutée à l'inventaire du joueur (`user.rewards`).
2. `user.rewardCount` est incrémenté de 1.
3. L'XP correspondant à `rarity.experienceGain` est ajouté (voir ci-dessous).
4. Les badges sont vérifiés et attribués si applicable.

### Inventaire de récompenses

`GET /api/me/rewards` retourne toutes les récompenses en possession du joueur authentifié.

`DELETE /api/me/rewards/{reward_id}` retire une récompense de l'inventaire. Retourne `404` si la récompense n'appartient pas à l'inventaire du joueur (même si elle existe en base).

---

## Progression du joueur

La logique de progression est centralisée dans `PlayerProgressService`.

### Gain d'expérience

`addExperience(user, amount)` :

1. Ajoute `amount` à `user.experience`.
2. Recherche en base le rang (`Rank`) dont `experienceMin <= nouvelleXP <= experienceMax`.
3. Si un rang est trouvé et qu'il est différent du rang actuel, `user.rank` est mis à jour.

Le rang est donc mis à jour automatiquement à chaque claim de récompense. Si aucun rang ne correspond à la plage d'XP (gap entre deux rangs), le rang actuel est conservé.

### Attribution des badges

`checkAndAwardBadges(user)` parcourt tous les badges en base et attribue ceux que le joueur ne possède pas encore et dont les conditions sont remplies.

La détection repose sur le nom du badge en français (`locale = 'fr'`) via une recherche par sous-chaîne. Les conditions sont les suivantes :

**Badges de participation** (basés sur `huntCount`) :

| Nom du badge (fr) | Condition |
|---|---|
| Chasseur débutant | huntCount >= 1 |
| Chasseur expert | huntCount >= 10 |
| Chasseur légendaire | huntCount >= 50 |
| Chasseur mythique | huntCount >= 100 |

**Badges de récompenses** (basés sur `rewardCount`) :

| Nom du badge (fr) | Condition |
|---|---|
| Premier butin | rewardCount >= 1 |
| Chasseur de trésors | rewardCount >= 10 |
| Collectionneur de reliques | rewardCount >= 50 |
| Maître des butins | rewardCount >= 100 |

**Badges d'ancienneté** (basés sur les jours depuis `user.createdAt`) :

| Nom du badge (fr) | Condition |
|---|---|
| 1 semaine | >= 7 jours |
| 1 mois | >= 30 jours |
| 1 an | >= 365 jours |
| 5 ans | >= 1825 jours |

Les badges déjà possédés sont ignorés. L'attribution est déclenchée lors de chaque participation et de chaque claim de récompense.

> La détection par sous-chaîne sur le nom français implique que le nom exact du badge en BDD doit contenir les libellés ci-dessus pour que les conditions s'appliquent.
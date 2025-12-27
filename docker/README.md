# Configuration Docker pour Lootopia

## Structure des fichiers

```
lootopia/
├── docker/
│   ├── docker-compose.yml
│   ├── setup.sh / setup.bat      ← Installation initiale
│   ├── start.sh / start.bat      ← Démarrage
│   ├── stop.sh / stop.bat        ← Arrêt
│   ├── logs.sh / logs.bat        ← Voir les logs
│   └── connect.sh / connect.bat  ← Se connecter à un conteneur
├── api/
│   └── [fichiers Symfony]
├── front-web/
│   └── [fichiers Vite/React]
└── front-mobile/
    └── [fichiers Expo/React Native]
```

## Services disponibles

| Service | URL | Description |
|---------|-----|-------------|
| API Symfony | http://localhost:8000/api/doc | Backend API |
| Front Web | http://localhost:5173 | Application web React |
| phpMyAdmin | http://localhost:8080 | Gestion de base de données |
| Mailpit | http://localhost:8025 | Interface de test des emails |
| MariaDB | localhost:3306 | Base de données |

## Configuration

### 1. Fichier `.env` pour Symfony (api/.env.local)

```env
DATABASE_URL="mysql://lootopia:lootopia@db:3306/lootopia?serverVersion=mariadb-10.4.32"
MAILER_DSN=smtp://mailpit:1025
```

### 2. Configuration Vite (front-web/.env)

```env
VITE_API_URL=http://localhost:8000
```

### 3. Démarrage

**PREMIÈRE INSTALLATION (une seule fois) :**

```bash
# Linux / macOS
chmod +x *.sh
./setup.sh

# Windows
setup.bat
```

Ce script va :
- Construire tous les conteneurs
- Créer la base de données
- Exécuter les migrations Symfony

Pour expo, il faut se connecter sur le container mobile pour lancer le serveur.

**DÉMARRAGES SUIVANTS :**

```bash
# Linux / macOS
./start.sh

# Windows
start.bat
```

### 4. Commandes utiles

**Scripts simplifiés :**

```bash
# Linux / macOS
./start.sh          # Démarrer tous les services
./stop.sh           # Arrêter tous les services
./logs.sh           # Voir tous les logs
./logs.sh api       # Voir les logs d'un service spécifique
./connect.sh api    # Se connecter au conteneur d'un service spécifique

# Windows
start.bat           # Démarrer tous les services
stop.bat            # Arrêter tous les services
logs.bat            # Voir tous les logs
logs.bat api        # Voir les logs d'un service spécifique
./connect.bat api   # Se connecter au conteneur d'un service spécifique
```

**Commandes Docker directes :**

```bash
# Accéder au conteneur API
docker exec -it lootopia_api bash

# Exécuter des commandes Symfony
docker exec -it lootopia_api php bin/console doctrine:migrations:migrate

# Installer des dépendances
docker exec -it lootopia_api composer install
docker exec -it lootopia_front_web npm install
docker exec -it lootopia_front_mobile npm install
```

## Identifiants par défaut

### Base de données
- Host: `db` (depuis les conteneurs) ou `localhost` (depuis l'hôte)
- Port: `3306`
- Database: `lootopia`
- User: `lootopia`
- Password: `lootopia`
- Root password: `root`

### phpMyAdmin
- URL: http://localhost:8080
- User: `root` ou `lootopia`
- Password: `root` ou `lootopia`

### Mailpit
- SMTP: `mailpit:1025` (depuis les conteneurs)
- Interface: http://localhost:8025

## Notes importantes

1. **Volumes**: Les fichiers sont montés en volumes, les modifications sont synchronisées en temps réel
2. **Expo**: Pour le front mobile, vous devrez peut-être scanner le QR code depuis votre téléphone avec l'app Expo Go
3. **Hot reload**: Activé par défaut pour Symfony et Vite


#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}  Lootopia - Installation      ${NC}"
echo -e "${BLUE}================================${NC}"
echo ""

# Rendre les scripts exécutables
echo -e "${YELLOW}Configuration des permissions...${NC}"
chmod +x start.sh stop.sh logs.sh setup.sh

echo -e "${YELLOW}Démarrage des conteneurs...${NC}"
docker compose up -d --build

echo ""
echo -e "${YELLOW}Attente du démarrage des services (30s)...${NC}"
sleep 30

echo ""
echo -e "${YELLOW}Configuration de la base de données Symfony...${NC}"

# Créer la base de données et la base de données de test
docker exec -it lootopia_api php bin/console doctrine:database:create --if-not-exists
docker exec -it lootopia_api php bin/console --env=test doctrine:database:drop --force --if-exists
docker exec -it lootopia_api php bin/console --env=test doctrine:database:create 
docker exec -it lootopia_api php bin/console --env=test doctrine:schema:create 

# Exécuter les migrations
docker exec -it lootopia_api php bin/console doctrine:migrations:migrate --no-interaction

# Charger les fixtures
docker exec -it lootopia_api php bin/console doctrine:fixtures:load --no-interaction

# Génerer la clé JWT
docker exec -it lootopia_api php bin/console lexik:jwt:generate-keypair

echo ""
echo -e "${GREEN} Installation terminée !${NC}"
echo ""
echo -e "${BLUE} Services disponibles :${NC}"
echo -e "  ${GREEN}-${NC} API Symfony      : ${YELLOW}http://localhost:8000/api/doc${NC} (30s pour démarrer)"
echo -e "  ${GREEN}-${NC} Front Web        : ${YELLOW}http://localhost:5173${NC}"
echo -e "  ${GREEN}-${NC} Front PWA        : ${YELLOW}https://localhost:5174${NC}"
echo -e "  ${GREEN}-${NC} phpMyAdmin       : ${YELLOW}http://localhost:8080${NC}"
echo -e "  ${GREEN}-${NC} Mailpit          : ${YELLOW}http://localhost:8025${NC}"
echo ""
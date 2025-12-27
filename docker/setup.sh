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
echo -e "${YELLOW}Attente du démarrage des services (45s)...${NC}"
sleep 45

echo ""
echo -e "${YELLOW}Configuration de la base de données Symfony...${NC}"

# Créer la base de données
docker exec -it lootopia_api php bin/console doctrine:database:create --if-not-exists

# Exécuter les migrations
docker exec -it lootopia_api php bin/console doctrine:migrations:migrate --no-interaction

echo ""
echo -e "${GREEN} Installation terminée !${NC}"
echo ""
echo -e "${BLUE} Services disponibles :${NC}"
echo -e "  ${GREEN}-${NC} API Symfony      : ${YELLOW}http://localhost:8000/api/doc${NC} (30s pour démarrer)"
echo -e "  ${GREEN}-${NC} Front Web        : ${YELLOW}http://localhost:5173${NC}"
echo -e "  ${GREEN}-${NC} phpMyAdmin       : ${YELLOW}http://localhost:8080${NC}"
echo -e "  ${GREEN}-${NC} Mailpit          : ${YELLOW}http://localhost:8025${NC}"
echo ""
echo -e "${BLUE} Commandes utiles :${NC}"
echo -e "  ${GREEN}-${NC} Démarrer        : ${YELLOW}./start.sh${NC}"
echo -e "  ${GREEN}-${NC} Arrêter         : ${YELLOW}./stop.sh${NC}"
echo -e "  ${GREEN}-${NC} Voir les logs   : ${YELLOW}./logs.sh${NC}"
echo ""
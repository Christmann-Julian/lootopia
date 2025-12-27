#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}  Lootopia - Démarrage Docker  ${NC}"
echo -e "${BLUE}================================${NC}"
echo ""

# Vérifier si Docker est installé
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Docker n'est pas installé${NC}"
    echo "Veuillez installer Docker depuis https://www.docker.com/get-started"
    exit 1
fi

# Vérifier si Docker Compose est installé
if ! command -v docker compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo -e "${RED}Docker Compose n'est pas installé${NC}"
    exit 1
fi

echo -e "${YELLOW}Démarrage des conteneurs...${NC}"
docker compose up -d

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}Conteneurs démarrés avec succès !${NC}"
    echo ""
    echo -e "${BLUE}Services disponibles :${NC}"
    echo -e "  ${GREEN}-${NC} API Symfony      : ${YELLOW}http://localhost:8000/api/doc${NC} (30s pour le démarrage)"
    echo -e "  ${GREEN}-${NC} Front Web        : ${YELLOW}http://localhost:5173${NC}"
    echo -e "  ${GREEN}-${NC} phpMyAdmin       : ${YELLOW}http://localhost:8080${NC}"
    echo -e "  ${GREEN}-${NC} Mailpit          : ${YELLOW}http://localhost:8025${NC}"
    echo ""
    echo -e "${BLUE}Voir les logs :${NC}"
    echo -e "  ${YELLOW}./logs.sh${NC}"
    echo ""
    echo -e "${BLUE}Arrêter les services :${NC}"
    echo -e "  ${YELLOW}./stop.sh${NC}"
    echo ""
else
    echo -e "${RED}Erreur lors du démarrage des conteneurs${NC}"
    exit 1
fi
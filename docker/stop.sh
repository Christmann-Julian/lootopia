#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}   Lootopia - Arrêt Docker     ${NC}"
echo -e "${BLUE}================================${NC}"
echo ""

echo -e "${YELLOW}Arrêt des conteneurs...${NC}"
docker-compose down

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}Conteneurs arrêtés avec succès !${NC}"
else
    echo -e "${RED}Erreur lors de l'arrêt des conteneurs${NC}"
    exit 1
fi
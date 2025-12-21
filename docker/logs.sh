#!/bin/bash

# Couleurs pour les messages
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}    Lootopia - Logs Docker     ${NC}"
echo -e "${BLUE}================================${NC}"
echo ""

# Si un argument est passé, afficher les logs d'un service spécifique
if [ $# -eq 0 ]; then
    echo "Affichage de tous les logs (Ctrl+C pour quitter)..."
    echo ""
    docker-compose logs -f
else
    echo "Affichage des logs de $1 (Ctrl+C pour quitter)..."
    echo ""
    docker-compose logs -f "$1"
fi
#!/bin/bash

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}================================${NC}"
echo -e "${BLUE}  Lootopia - Connexion Docker  ${NC}"
echo -e "${BLUE}================================${NC}"
echo ""

# Fonction pour afficher l'aide
show_help() {
    echo -e "${YELLOW}Usage:${NC} ./connect.sh [service]"
    echo ""
    echo -e "${YELLOW}Services disponibles:${NC}"
    echo -e "  ${GREEN}api${NC}           - Se connecter au conteneur API (Symfony)"
    echo -e "  ${GREEN}web${NC}           - Se connecter au conteneur Front Web (Vite)"
    echo -e "  ${GREEN}pwa${NC}           - Se connecter au conteneur Front PWA (Vite)"
    echo -e "  ${GREEN}db${NC}            - Se connecter à la base de données MariaDB"
    echo ""
    echo -e "${YELLOW}Exemples:${NC}"
    echo -e "  ./connect.sh api"
    echo -e "  ./connect.sh web"
    echo ""
}

# Vérifier si un argument est fourni
if [ $# -eq 0 ]; then
    show_help
    exit 1
fi

SERVICE=$1

case $SERVICE in
    api)
        echo -e "${GREEN}Connexion au conteneur API...${NC}"
        echo ""
        docker exec -it lootopia_api bash
        ;;
    web)
        echo -e "${GREEN}Connexion au conteneur Front Web...${NC}"
        echo ""
        docker exec -it lootopia_front_web sh
        ;;
    pwa)
        echo -e "${GREEN}Connexion au conteneur Front PWA...${NC}"
        echo ""
        docker exec -it lootopia_front_pwa sh
        ;;
    db)
        echo -e "${GREEN}Connexion à la base de données MariaDB...${NC}"
        echo ""
        docker exec -it lootopia_db mysql -uroot -proot
        ;;
    *)
        echo -e "${RED}Service inconnu: $SERVICE${NC}"
        echo ""
        show_help
        exit 1
        ;;
esac
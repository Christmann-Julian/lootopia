@echo off
chcp 65001 >nul

echo ================================
echo   Lootopia - Installation      
echo ================================
echo.

echo  Démarrage des conteneurs...
docker compose up -d --build

echo.
echo  Attente du démarrage des services (45s)...
timeout /t 45 /nobreak >nul

echo.
echo   Configuration de la base de données Symfony...

REM Créer la base de données
docker exec -it lootopia_api php bin/console doctrine:database:create --if-not-exists

REM Exécuter les migrations
docker exec -it lootopia_api php bin/console doctrine:migrations:migrate --no-interaction

REM Charger les fixtures
docker exec -it lootopia_api php bin/console doctrine:fixtures:load --no-interaction

REM Générer la clé JWT
docker exec -it lootopia_api php bin/console lexik:jwt:generate-keypair

echo.
echo  Installation terminée !
echo.
echo  Services disponibles :
echo   - API Symfony      : http://localhost:8000/api/doc (30s pour démarrer)
echo   - Front Web        : http://localhost:5173
echo   - phpMyAdmin       : http://localhost:8080
echo   - Mailpit          : http://localhost:8025
echo.

pause
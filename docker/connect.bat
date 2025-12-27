@echo off
chcp 65001 >nul

echo ================================
echo   Lootopia - Connexion Docker  
echo ================================
echo.

REM Vérifier si un argument est fourni
if "%~1"=="" goto show_help

set SERVICE=%1

if "%SERVICE%"=="api" goto connect_api
if "%SERVICE%"=="web" goto connect_web
if "%SERVICE%"=="mobile" goto connect_mobile
if "%SERVICE%"=="db" goto connect_db

echo Service inconnu: %SERVICE%
echo.
goto show_help

:connect_api
echo Connexion au conteneur API...
echo.
docker exec -it lootopia_api bash
goto end

:connect_web
echo Connexion au conteneur Front Web...
echo.
docker exec -it lootopia_front_web sh
goto end

:connect_mobile
echo Connexion au conteneur Front Mobile...
echo.
docker exec -it lootopia_front_mobile bash
goto end

:connect_db
echo Connexion à la base de données MariaDB...
echo.
docker exec -it lootopia_db mysql -uroot -proot
goto end

:show_help
echo Usage: connect.bat [service]
echo.
echo Services disponibles:
echo   api           - Se connecter au conteneur API (Symfony)
echo   web           - Se connecter au conteneur Front Web (Vite)
echo   mobile        - Se connecter au conteneur Front Mobile (Expo)
echo   db            - Se connecter à la base de données MariaDB
echo.
echo Exemples:
echo   connect.bat api
echo   connect.bat web
echo.
goto end

:end
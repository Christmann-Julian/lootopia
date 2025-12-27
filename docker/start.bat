@echo off
chcp 65001 >nul
setlocal enabledelayedexpansion

echo ================================
echo   Lootopia - Démarrage Docker  
echo ================================
echo.

REM Vérifier si Docker est installé
docker --version >nul 2>&1
if errorlevel 1 (
    echo Docker n'est pas installé
    echo Veuillez installer Docker depuis https://www.docker.com/get-started
    pause
    exit /b 1
)

REM Vérifier si Docker Compose est installé
docker-compose --version >nul 2>&1
if errorlevel 1 (
    docker compose version >nul 2>&1
    if errorlevel 1 (
        echo Docker Compose n'est pas installé
        pause
        exit /b 1
    )
)

echo Démarrage des conteneurs...
docker-compose up -d

set "exitCode=%ERRORLEVEL%"

if "%exitCode%"=="0" (
    echo.
    echo Conteneurs démarrés avec succès !
    echo.
    echo Services disponibles :
    echo   - API Symfony      : http://localhost:8000/api/doc (30s pour le démarrage)
    echo   - Front Web        : http://localhost:5173
    echo   - phpMyAdmin       : http://localhost:8080
    echo   - Mailpit          : http://localhost:8025
    echo.
    echo Voir les logs :
    echo   logs.bat
    echo.
    echo Arrêter les services :
    echo   stop.bat
    echo.
) else (
    echo Erreur lors du démarrage des conteneurs
    echo lancer docker-compose logs pour diagnostiquer le problème
    pause
    exit /b %exitCode%
)

pause
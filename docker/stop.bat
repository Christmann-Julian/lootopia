@echo off
chcp 65001 >nul

echo ================================
echo    Lootopia - Arrêt Docker     
echo ================================
echo.

echo Arrêt des conteneurs...
docker-compose down

if errorlevel 0 (
    echo.
    echo Conteneurs arrêtés avec succès !
) else (
    echo Erreur lors de l'arrêt des conteneurs
    pause
    exit /b 1
)

pause
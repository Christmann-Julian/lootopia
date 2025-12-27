@echo off
chcp 65001 >nul

echo ================================
echo     Lootopia - Logs Docker     
echo ================================
echo.

REM Si un argument est passé, afficher les logs d'un service spécifique
if "%~1"=="" goto all_logs

echo Affichage des logs de %1 (Ctrl+C pour quitter)...
echo.
docker compose logs -f %1
goto end

:all_logs
echo Affichage de tous les logs (Ctrl+C pour quitter)...
echo.
docker compose logs -f

:end
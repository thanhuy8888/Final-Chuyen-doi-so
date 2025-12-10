@echo off
echo ========================================
echo   MSDB - Management System Dashboard
echo   Database Import Script
echo ========================================
echo.

REM Try to find MySQL in common Laragon locations
set MYSQL_PATH=

if exist "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" (
    set MYSQL_PATH=C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe
)

if exist "C:\laragon\bin\mysql\mysql-5.7.24-winx64\bin\mysql.exe" (
    set MYSQL_PATH=C:\laragon\bin\mysql\mysql-5.7.24-winx64\bin\mysql.exe
)

REM Check for other versions
for /d %%i in (C:\laragon\bin\mysql\*) do (
    if exist "%%i\bin\mysql.exe" (
        set MYSQL_PATH=%%i\bin\mysql.exe
    )
)

if "%MYSQL_PATH%"=="" (
    echo ERROR: MySQL not found in Laragon!
    echo Please make sure Laragon is installed properly.
    echo.
    echo Alternative: Import database.sql manually using:
    echo - HeidiSQL (included with Laragon)
    echo - phpMyAdmin (http://localhost/phpmyadmin)
    echo.
    pause
    exit /b 1
)

echo Found MySQL at: %MYSQL_PATH%
echo.
echo Importing database...
"%MYSQL_PATH%" -u root -e "SOURCE database.sql"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo   SUCCESS! Database imported!
    echo ========================================
    echo.
    echo You can now access the dashboard at:
    echo   http://localhost/Finalchuyendoiso
    echo.
    echo Login credentials:
    echo   Username: admin
    echo   Password: admin123
    echo.
) else (
    echo.
    echo ========================================
    echo   ERROR: Import failed!
    echo ========================================
    echo.
    echo Please try manually:
    echo 1. Open HeidiSQL from Laragon
    echo 2. File -^> Run SQL file...
    echo 3. Select database.sql
    echo 4. Click Execute
    echo.
)

pause

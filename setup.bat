@echo off
chcp 65001 > nul
setlocal enabledelayedexpansion

echo.
echo 🚀 Инициализация проекта MSCopilit-shop...
echo.

REM Проверка наличия composer
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Composer не установлен. Установи его перед запуском скрипта.
    pause
    exit /b 1
)

REM Проверка наличия php
where php >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ❌ PHP не установлен. Установи его перед запуском скрипта.
    pause
    exit /b 1
)

REM Проверка наличия npm
where npm >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Node.js/npm не установлены. Установи их перед запуском скрипта.
    pause
    exit /b 1
)

echo ✅ Все требования установлены
echo.

REM Установка Composer зависимостей
echo 📦 Установка Composer зависимостей...
call composer install
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Ошибка при установке Composer зависимостей
    pause
    exit /b 1
)
echo ✅ Composer зависимости установлены
echo.

REM Установка NPM зависимостей
echo 📦 Установка NPM зависимостей...
call npm install
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Ошибка при установке NPM зависимостей
    pause
    exit /b 1
)
echo ✅ NPM зависимости установлены
echo.

REM Копирование .env файла
echo 📝 Копирование .env файла...
if not exist .env (
    copy .env.example .env
    echo ✅ .env файл скопирован
) else (
    echo ⚠️  .env файл уже существует, пропускаю копирование
)
echo.

REM Генерирование APP_KEY
echo 🔑 Генерирование APP_KEY...
php artisan key:generate
echo ✅ APP_KEY сгенерирован
echo.

REM Миграции и seeding
echo 🗄️  Запуск миграций и seeding...
php artisan migrate:fresh --seed
if %ERRORLEVEL% NEQ 0 (
    echo ❌ Ошибка при выполнении миграций
    pause
    exit /b 1
)
echo ✅ Миграции и seeding выполнены
echo.

echo 🎉 Проект успешно инициализирован!
echo.
echo 📌 Дальнейшие шаги:
echo    1. Запусти: npm run dev
echo    2. Открой: http://localhost:5173
echo.

pause

#!/bin/bash

echo "🚀 Инициализация проекта MSCopilit-shop..."
echo ""

# Проверка наличия composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer не установлен. Установи его перед запуском скрипта."
    exit 1
fi

# Проверка наличия php
if ! command -v php &> /dev/null; then
    echo "❌ PHP не установлен. Установи его перед запуском скрипта."
    exit 1
fi

# Проверка наличия npm
if ! command -v npm &> /dev/null; then
    echo "❌ Node.js/npm не установлены. Установи их перед запуском скрипта."
    exit 1
fi

echo "✅ Все требования установлены"
echo ""

# Установка Composer зависимостей
echo "📦 Установка Composer зависимостей..."
composer install
if [ $? -ne 0 ]; then
    echo "❌ Ошибка при установке Composer зависимостей"
    exit 1
fi
echo "✅ Composer зависимости установлены"
echo ""

# Установка NPM зависимостей
echo "📦 Установка NPM зависимостей..."
npm install
if [ $? -ne 0 ]; then
    echo "❌ Ошибка при установке NPM зависимостей"
    exit 1
fi
echo "✅ NPM зависимости установлены"
echo ""

# Копирование .env файла
echo "📝 Копирование .env файла..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ .env файл скопирован"
else
    echo "⚠️  .env файл уже существует, пропускаю копирование"
fi
echo ""

# Генерирование APP_KEY
echo "🔑 Генерирование APP_KEY..."
php artisan key:generate
echo "✅ APP_KEY сгенерирован"
echo ""

# Миграции и seeding
echo "🗄️  Запуск миграций и seeding..."
php artisan migrate:fresh --seed
if [ $? -ne 0 ]; then
    echo "❌ Ошибка при выполнении миграций"
    exit 1
fi
echo "✅ Миграции и seeding выполнены"
echo ""

echo "🎉 Проект успешно инициализирован!"
echo ""
echo "📌 Дальнейшие шаги:"
echo "   1. Запусти: npm run dev"
echo "   2. Открой: http://localhost:5173"
echo ""

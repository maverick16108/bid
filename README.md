# Проект «Новосталь-М» B2B

Информационная система для сбора заявок холдинга «Новосталь-М».

## Структура проекта

- `/backend` — Laravel 11 API.
- `/frontend` — Vue 3 SPA (Vite, TypeScript).
- `/docs` — Документация проекта (ТЗ, Архитектура и т.д.).
- `/assets` — Статические ресурсы (логотипы).

## Требования

- PHP 8.2+
- Node.js 20+
- PostgreSQL
- Nginx

## Быстрый старт

### Backend
1. `cd backend`
2. `composer install`
3. `php artisan key:generate`
4. `php artisan migrate`

### Frontend
1. `cd frontend`
2. `npm install`
3. `npm run dev`

## Особенности
- Вход по номеру телефона (SMS OTP).
- Интеграция с 1С через HTTP/JSON.
- 1С — единственный источник истины для статусов и спецификаций.
1
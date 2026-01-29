---
description: Deployment workflow for Debian + nginx environment.
---

# DEPLOYMENT.md

## Схема
- Debian
- nginx
- bid.prema.su
- SPA + /api

## Frontend
- сборка в frontend/dist
- SPA fallback

## Backend
- backend/public
- PostgreSQL

## nginx
- root → frontend/dist
- /api → backend/public
- PHP-FPM

## Проверка
- SPA открывается
- /api/health работает
- прямые SPA роуты работают

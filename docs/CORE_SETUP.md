# BaoSon Platform Core – Setup Guide (Core Seed)

## Requirements
- PHP 8.3+
- Composer
- Node.js 18+
- pnpm
- SQLite (local dev)
- Windows 10 Pro (Developer Mode ON)

## 1. Install dependencies
Run at repository root:

    pnpm install

Install PHP dependencies (ONLY for control-plane):

    cd apps/control-plane
    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate

## 2. Development
Run admin (Inertia + React):

    pnpm dev

Laravel serve (optional):

    cd apps/control-plane
    php artisan serve

## 3. Common commands
From root:

    pnpm dev                  # Dev admin UI
    pnpm build                # Build admin assets
    pnpm cp:migrate           # Run migrations
    pnpm cp:seed              # Seed database
    pnpm cp:test              # Run Laravel tests
    pnpm clean                # Clean node_modules & vendor

## 4. Important rules
- NEVER run composer at repository root
- NEVER run php artisan at repository root
- Always use pnpm at root for JS tasks
- Laravel lives in apps/control-plane

This repository is the CORE SEED for BaoSon SaaS Platform.

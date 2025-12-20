# Baoson Platform Core

A production-ready SaaS platform core built with Laravel 12, Inertia.js, React, and TypeScript, organized as a monorepo.

## 🚀 Quick Start

See [docs/CORE_SETUP.md](./docs/CORE_SETUP.md) for detailed setup instructions.

**10-minute setup with SQLite:**

```bash
# 1. Install dependencies
cd apps/control-plane && composer install && cd ../..
pnpm install

# 2. Setup environment
cp apps/control-plane/.env.example apps/control-plane/.env
cd apps/control-plane && php artisan key:generate && cd ../..

# 3. Setup database
touch apps/control-plane/database/database.sqlite
cd apps/control-plane && php artisan migrate && cd ../..

# 4. Build assets
pnpm build:control-plane

# 5. Start servers
# Terminal 1:
cd apps/control-plane && php artisan serve

# Terminal 2 (from root):
pnpm dev:control-plane
```

Visit http://localhost:8000

## 📁 Monorepo Structure

```
baoson-platform-core/
├── apps/
│   └── control-plane/          # Laravel 12 main application
├── packages/
│   ├── shared-types/           # TypeScript shared contracts
│   └── ui-admin/               # Reusable admin UI components
├── infra/
│   └── scripts/                # Development scripts
└── docs/                       # Documentation
```

## 🛠️ Available Commands

### From Monorepo Root

```bash
pnpm dev:control-plane      # Start Vite dev server
pnpm build:control-plane    # Build frontend assets
pnpm typecheck             # Type check all packages
pnpm test:control-plane    # Run Laravel tests
```

### From apps/control-plane

```bash
php artisan serve           # Start Laravel server
php artisan migrate         # Run migrations
php artisan test           # Run tests
```

## 📚 Documentation

- [Core Setup Guide](./docs/CORE_SETUP.md) - Detailed setup instructions
- [Security Policy](./docs/SECURITY.md) - Security guidelines
- [ADR](./docs/ADR/) - Architecture Decision Records

## 🏗️ Tech Stack

- **Backend**: Laravel 12
- **Frontend**: React 19 + TypeScript + Inertia.js
- **Build Tool**: Vite
- **Package Manager**: pnpm (workspaces)
- **Database**: SQLite (dev) / PostgreSQL / MySQL

## 📦 Packages

### @baoson/shared-types

TypeScript type definitions for:
- RBAC (Roles & Permissions)
- Menu & Navigation
- i18n (Internationalization)
- Multi-tenancy
- Licensing
- API Contracts

### @baoson/ui-admin

Reusable admin UI components (scaffold phase).

## 🔧 Development

### Prerequisites

- PHP >= 8.2
- Composer >= 2.8
- Node.js >= 18
- pnpm >= 8

### Scripts

See `infra/scripts/` for automation:
- `dev-sqlite.sh` - Complete SQLite setup
- `migrate.sh` - Database migrations
- `seed.sh` - Database seeding

## 📝 License

[Your License Here]

## 🤝 Contributing

[Contributing Guidelines]


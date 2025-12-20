#!/bin/bash
# Development setup script for SQLite
# Usage: ./infra/scripts/dev-sqlite.sh

set -e

echo "🚀 Setting up Baoson Platform Core (SQLite)..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Navigate to control-plane
cd "$(dirname "$0")/../../apps/control-plane" || exit 1

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠️  .env file not found. Copying from .env.example...${NC}"
    cp .env.example .env
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
    echo -e "${YELLOW}🔑 Generating application key...${NC}"
    php artisan key:generate
fi

# Ensure SQLite database exists
if [ ! -f database/database.sqlite ]; then
    echo -e "${YELLOW}📦 Creating SQLite database...${NC}"
    touch database/database.sqlite
fi

# Run migrations
echo -e "${YELLOW}🔄 Running migrations...${NC}"
php artisan migrate --force

# Run seeders (if any)
if [ -f database/seeders/DatabaseSeeder.php ]; then
    echo -e "${YELLOW}🌱 Seeding database...${NC}"
    php artisan db:seed --force || echo "No seeders to run"
fi

# Set permissions
echo -e "${YELLOW}🔐 Setting storage permissions...${NC}"
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo -e "${GREEN}✅ Setup complete!${NC}"
echo ""
echo "Next steps:"
echo "  1. Start Laravel: cd apps/control-plane && php artisan serve"
echo "  2. Start Vite: pnpm dev:control-plane (from root)"
echo "  3. Visit: http://localhost:8000"


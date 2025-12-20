#!/bin/bash
# Database migration script
# Usage: ./infra/scripts/migrate.sh [fresh|refresh|rollback]

set -e

cd "$(dirname "$0")/../../apps/control-plane" || exit 1

case "${1:-}" in
    fresh)
        echo "🔄 Fresh migration (drop all tables and re-run)..."
        php artisan migrate:fresh --force
        ;;
    refresh)
        echo "🔄 Refresh migration (rollback and re-run)..."
        php artisan migrate:refresh --force
        ;;
    rollback)
        echo "⏪ Rolling back last migration..."
        php artisan migrate:rollback --force
        ;;
    *)
        echo "🔄 Running migrations..."
        php artisan migrate --force
        ;;
esac

echo "✅ Migration complete!"


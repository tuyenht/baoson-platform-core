#!/bin/bash
# Database seeding script
# Usage: ./infra/scripts/seed.sh [class]

set -e

cd "$(dirname "$0")/../../apps/control-plane" || exit 1

if [ -n "${1:-}" ]; then
    echo "🌱 Seeding specific class: $1"
    php artisan db:seed --class="$1" --force
else
    echo "🌱 Seeding database..."
    php artisan db:seed --force
fi

echo "✅ Seeding complete!"


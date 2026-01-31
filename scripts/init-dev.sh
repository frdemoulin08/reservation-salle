#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

if [ -f "$ROOT_DIR/.env.example" ] && [ ! -f "$ROOT_DIR/.env.local" ]; then
  cp "$ROOT_DIR/.env.example" "$ROOT_DIR/.env.local"
  echo "Created .env.local from .env.example"
fi

cd "$ROOT_DIR"

echo "Installing PHP dependencies..."
composer install

echo "Installing Node dependencies..."
npm install

echo "Building assets (dev)..."
npm run dev

echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "Loading fixtures (purges DB)..."
php bin/console doctrine:fixtures:load --no-interaction

echo "Init complete."

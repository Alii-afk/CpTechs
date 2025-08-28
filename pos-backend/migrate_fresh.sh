#!/bin/bash

echo "🚀 Starting fresh migration for CP Tech POS..."
echo ""

# Clear any existing database
echo "🗑️  Clearing existing database..."
php artisan migrate:reset --force

# Run migrations in correct order
echo "📦 Running migrations..."
php artisan migrate --force

# Run seeders
echo "🌱 Running seeders..."
php artisan db:seed --force

echo ""
echo "✅ Migration completed successfully!"
echo "🎉 Your CP Tech POS system is ready!" 
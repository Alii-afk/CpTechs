#!/bin/bash

echo "🚀 Setting up CP Tech POS on a new PC..."
echo ""

# Check if database exists and drop it completely
echo "🗑️  Dropping existing database if it exists..."
mysql -u root -p -e "DROP DATABASE IF EXISTS pos_backend; CREATE DATABASE pos_backend CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || {
    echo "⚠️  Could not drop database. You may need to manually drop the 'pos_backend' database."
    echo "   Run: mysql -u root -p -e 'DROP DATABASE IF EXISTS pos_backend; CREATE DATABASE pos_backend;'"
    read -p "Press Enter after manually dropping the database..."
}

# Clear Laravel caches
echo "🧹 Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run fresh migration
echo "📦 Running fresh migrations..."
php artisan migrate:fresh --force

# Run seeders
echo "🌱 Running seeders..."
php artisan db:seed --force

echo ""
echo "✅ Setup completed successfully!"
echo "🎉 Your CP Tech POS system is ready!"
echo ""
echo "📋 Next steps:"
echo "1. Start the server: php artisan serve --port=8001"
echo "2. Open your browser to: http://localhost:8001"
echo "3. Access the frontend from the pos-frontend directory" 
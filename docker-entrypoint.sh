#!/bin/bash

# Script de inicialización para el contenedor Laravel
set -e

echo "🚀 Iniciando Grid Bot Helper..."

# Esperar un momento para que el sistema esté listo
sleep 2

# Crear archivo .env si no existe
if [ ! -f ".env" ]; then
    echo "📝 Creando archivo .env..."
    cp .env.example .env
fi

# Generar clave de aplicación si no existe
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generando clave de aplicación..."
    php artisan key:generate --force
fi

# Crear directorio de base de datos si no existe
echo "📁 Configurando base de datos..."
mkdir -p /var/www/html/database

# Crear archivo de base de datos SQLite si no existe
if [ ! -f "/var/www/html/database/database.sqlite" ]; then
    echo "🗄️ Creando base de datos SQLite..."
    touch /var/www/html/database/database.sqlite
    chown www-data:www-data /var/www/html/database/database.sqlite
    chmod 664 /var/www/html/database/database.sqlite
fi

# Ejecutar migraciones
echo "🔄 Ejecutando migraciones..."
php artisan migrate --force

# Limpiar y optimizar cachés
echo "🧹 Optimizando aplicación..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Configurar permisos finales
echo "🔐 Configurando permisos..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

echo "✅ Grid Bot Helper está listo!"
echo "🌐 La aplicación estará disponible en http://localhost"

# Iniciar Apache
exec apache2-foreground
#!/bin/bash

# Script de despliegue para Grid Bot Helper
# Ejecutar desde el directorio de la aplicación: bash deploy.sh

set -e

echo "🚀 Iniciando despliegue de Grid Bot Helper..."

# Verificar que estamos en el directorio correcto
if [ ! -f "composer.json" ]; then
    echo "❌ Error: No se encontró composer.json. Ejecuta este script desde el directorio raíz de la aplicación."
    exit 1
fi

# Verificar que tenemos permisos de escritura
if [ ! -w "." ]; then
    echo "❌ Error: No tienes permisos de escritura en este directorio."
    exit 1
fi

echo "📦 Instalando dependencias de PHP..."
composer install --optimize-autoloader --no-dev --no-interaction

echo "📦 Instalando dependencias de Node.js..."
npm ci

echo "🏗️ Compilando assets para producción..."
NODE_ENV=production npm run build

echo "⚙️ Configurando Laravel..."

# Copiar archivo de entorno de producción
if [ ! -f ".env" ]; then
    cp .env.production .env
    echo "📝 Archivo .env creado desde .env.production"
else
    echo "📝 Archivo .env ya existe, no se sobrescribe"
fi

# Generar clave de aplicación si no existe
if ! grep -q "APP_KEY=base64:" .env; then
    php artisan key:generate --force
    echo "🔑 Clave de aplicación generada"
fi

# Ejecutar migraciones
echo "🗄️ Ejecutando migraciones de base de datos..."
php artisan migrate --force

# Limpiar y optimizar cachés
echo "🧹 Limpiando cachés..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "⚡ Optimizando para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Configurar permisos
echo "🔐 Configurando permisos..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Reiniciar servicios
echo "🔄 Reiniciando servicios..."
systemctl reload php8.2-fpm
systemctl reload nginx

echo "✅ Despliegue completado exitosamente!"
echo ""
echo "🌐 Tu aplicación debería estar disponible en: https://gridbothelper.duckdns.org"
echo ""
echo "📋 Si es la primera vez, no olvides:"
echo "1. Configurar la base de datos en .env"
echo "2. Obtener certificado SSL: sudo certbot --nginx -d gridbothelper.duckdns.org"
echo "3. Verificar que DuckDNS esté apuntando a tu IP"
echo ""
echo "🎉 ¡Listo!"
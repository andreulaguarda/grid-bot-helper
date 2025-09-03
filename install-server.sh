#!/bin/bash

# Script de instalación para Grid Bot Helper sin Docker
# Ejecutar como: sudo bash install-server.sh

set -e

echo "🚀 Iniciando instalación de Grid Bot Helper sin Docker..."

# Actualizar sistema
echo "📦 Actualizando sistema..."
apt update && apt upgrade -y

# Instalar PHP 8.2 y extensiones necesarias
echo "🐘 Instalando PHP 8.2 y extensiones..."
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-zip php8.2-mbstring php8.2-gd php8.2-bcmath php8.2-intl php8.2-redis php8.2-sqlite3

# Instalar Composer
echo "🎼 Instalando Composer..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
fi

# Instalar Node.js 18.x
echo "📦 Instalando Node.js..."
if ! command -v node &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs
fi

# Instalar Nginx
echo "🌐 Instalando Nginx..."
apt install -y nginx

# Instalar MySQL (opcional, comentado por si ya tienes base de datos)
# echo "🗄️ Instalando MySQL..."
# apt install -y mysql-server
# mysql_secure_installation

# Instalar Certbot para SSL
echo "🔒 Instalando Certbot..."
apt install -y certbot python3-certbot-nginx

# Crear directorio de la aplicación
echo "📁 Configurando directorio de aplicación..."
mkdir -p /var/www/html
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

# Configurar PHP-FPM
echo "⚙️ Configurando PHP-FPM..."
systemctl enable php8.2-fpm
systemctl start php8.2-fpm

# Configurar Nginx
echo "🌐 Configurando Nginx..."
cp nginx-site.conf /etc/nginx/sites-available/gridbothelper
ln -sf /etc/nginx/sites-available/gridbothelper /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl enable nginx
systemctl restart nginx

# Configurar firewall
echo "🔥 Configurando firewall..."
ufw allow 'Nginx Full'
ufw allow OpenSSH
ufw --force enable

echo "✅ Instalación base completada!"
echo ""
echo "📋 PRÓXIMOS PASOS:"
echo "1. Clonar el repositorio en /var/www/html"
echo "2. Ejecutar: composer install --optimize-autoloader --no-dev"
echo "3. Ejecutar: npm install && npm run build"
echo "4. Configurar .env con datos de base de datos"
echo "5. Ejecutar: php artisan key:generate"
echo "6. Ejecutar: php artisan migrate"
echo "7. Obtener certificado SSL: sudo certbot --nginx -d gridbothelper.duckdns.org"
echo "8. Configurar permisos: sudo chown -R www-data:www-data /var/www/html"
echo ""
echo "🎉 ¡Listo para desplegar!"
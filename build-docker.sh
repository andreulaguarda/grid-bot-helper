#!/bin/bash

# Script para construir la imagen Docker de Grid Bot Helper
# Este script optimiza el proceso de construcción y proporciona mejor feedback

set -e  # Salir si hay algún error

echo "🚀 Iniciando construcción de Grid Bot Helper..."
echo "📦 Limpiando imágenes anteriores..."

# Limpiar imágenes anteriores si existen
docker compose down --remove-orphans 2>/dev/null || true
docker image prune -f 2>/dev/null || true

echo "🔧 Construyendo imagen Docker..."
echo "⏳ Esto puede tomar varios minutos la primera vez..."
echo "💡 Las advertencias de debconf son normales y se pueden ignorar"
echo "📝 La línea 'version' del docker-compose.yml ha sido eliminada (obsoleta)"

# Construir con progreso simple y sin cache para asegurar una construcción limpia
docker compose build --no-cache --progress=plain

echo "✅ Imagen construida exitosamente!"
echo "🚀 Iniciando contenedores..."

# Iniciar los contenedores
docker compose up -d

echo "⏳ Esperando que la aplicación esté lista..."
sleep 10

echo "🔍 Verificando estado de los contenedores..."
docker compose ps

echo ""
echo "✅ ¡Despliegue completado!"
echo "🌐 La aplicación debería estar disponible en:"
echo "   - http://localhost:8080 (si estás ejecutando localmente)"
echo "   - http://[IP-DE-TU-SERVIDOR]:8080 (si estás en un servidor)"
echo ""
echo "📋 Comandos útiles:"
echo "   - Ver logs: docker compose logs -f"
echo "   - Parar: docker compose down"
echo "   - Reiniciar: docker compose restart"
echo "   - Entrar al contenedor: docker compose exec app bash"
echo ""
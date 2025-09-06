# Script para construir la imagen Docker de Grid Bot Helper en Windows
# Este script optimiza el proceso de construcción y proporciona mejor feedback

$ErrorActionPreference = "Stop"

Write-Host "🚀 Iniciando construcción de Grid Bot Helper..." -ForegroundColor Green
Write-Host "📦 Limpiando imágenes anteriores..." -ForegroundColor Yellow

# Limpiar contenedores e imágenes anteriores si existen
try {
    docker compose down --remove-orphans 2>$null
    docker image prune -f 2>$null
} catch {
    Write-Host "No hay contenedores previos para limpiar" -ForegroundColor Gray
}

Write-Host "ℹ️ Información importante sobre la construcción:" -ForegroundColor Blue
Write-Host "1. La primera construcción puede tardar varios minutos (instalación de dependencias)" -ForegroundColor Yellow
Write-Host "2. Las reconstrucciones posteriores serán más rápidas gracias al caché de capas" -ForegroundColor Yellow
Write-Host "3. Las advertencias de debconf son normales y no afectan al funcionamiento" -ForegroundColor Cyan

# Generar certificados SSL si no existen
if (-not (Test-Path "ssl/certificate.crt") -or -not (Test-Path "ssl/private.key")) {
    Write-Host "🔒 Generando certificados SSL..." -ForegroundColor Yellow
    bash ssl/generate-certs.sh
}

# Construir con progreso simple
try {
    docker compose build --progress=plain
    Write-Host "✅ Imagen construida exitosamente!" -ForegroundColor Green
} catch {
    Write-Host "❌ Error durante la construcción. Revisa los logs arriba." -ForegroundColor Red
    exit 1
}

Write-Host "🚀 Iniciando contenedores..." -ForegroundColor Blue

# Iniciar los contenedores
try {
    docker compose up -d
    Write-Host "✅ Contenedores iniciados!" -ForegroundColor Green
} catch {
    Write-Host "❌ Error al iniciar contenedores." -ForegroundColor Red
    exit 1
}

Write-Host "⏳ Esperando que la aplicación esté lista..." -ForegroundColor Yellow
Start-Sleep -Seconds 15

Write-Host "🔍 Verificando estado de los contenedores..." -ForegroundColor Blue
docker compose ps

Write-Host ""
Write-Host "✅ ¡Despliegue completado!" -ForegroundColor Green
Write-Host "🌐 La aplicación debería estar disponible en:" -ForegroundColor Cyan
Write-Host "   - http://localhost:8080 (acceso HTTP)" -ForegroundColor White
Write-Host "   - https://localhost:8443 (acceso HTTPS recomendado)" -ForegroundColor White
Write-Host "
Nota: Al acceder por HTTPS, verás una advertencia de seguridad por el certificado autofirmado.
Esto es normal en entornos de desarrollo." -ForegroundColor Yellow
Write-Host ""
Write-Host "📋 Comandos útiles:" -ForegroundColor Cyan
Write-Host "   - Ver logs: docker compose logs -f" -ForegroundColor White
Write-Host "   - Parar: docker compose down" -ForegroundColor White
Write-Host "   - Reiniciar: docker compose restart" -ForegroundColor White
Write-Host "   - Entrar al contenedor: docker compose exec app bash" -ForegroundColor White
Write-Host ""
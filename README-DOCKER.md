# 🐳 Despliegue con Docker - Grid Bot Helper

Esta guía te ayudará a desplegar la aplicación Grid Bot Helper usando Docker, evitando problemas de permisos y configuración del servidor.

## 📋 Prerrequisitos

- Docker instalado en tu servidor
- Docker Compose instalado
- Git para clonar el repositorio

## 🚀 Despliegue Rápido

### 1. Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/grid-bot-helper.git
cd grid-bot-helper
```

### 2. Configurar variables de entorno
```bash
cp .env.docker .env
```

### 3. Construir y ejecutar

**Opción A: Usando el script automatizado (Recomendado)**

En Linux/Mac:
```bash
chmod +x build-docker.sh
./build-docker.sh
```

En Windows (PowerShell):
```powershell
.\build-docker.ps1
```

**Opción B: Comandos manuales**
```bash
docker compose up -d --build
```

> **Nota:** Durante la construcción verás advertencias de `debconf` como "unable to initialize frontend". Estas son normales y no afectan el funcionamiento de la aplicación.

### 4. Verificar el despliegue
```bash
# Ver logs del contenedor
docker-compose logs -f app

# Verificar que el contenedor esté ejecutándose
docker-compose ps
```

## 🌐 Acceso a la Aplicación

- **URL Principal:** http://tu-servidor-ip
- **Puerto:** 80 (HTTP)

## 🛠️ Comandos Útiles

### Gestión del Contenedor
```bash
# Iniciar servicios
docker-compose up -d

# Detener servicios
docker-compose down

# Reiniciar servicios
docker-compose restart

# Ver logs en tiempo real
docker-compose logs -f app

# Acceder al contenedor
docker-compose exec app bash
```

### Comandos Laravel dentro del contenedor
```bash
# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Limpiar cachés
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Generar nueva clave de aplicación
docker-compose exec app php artisan key:generate
```

## 🔧 Configuración Avanzada

### Variables de Entorno Importantes

En el archivo `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:tu-clave-generada
DB_CONNECTION=sqlite
DB_DATABASE=/var/www/html/database/database.sqlite
```

### Personalizar Puertos

En `docker-compose.yml`, modifica la sección de puertos:
```yaml
ports:
  - "8080:80"  # Cambiar 8080 por el puerto deseado
```

### Usar con Nginx (Opcional)

Para mejor rendimiento, puedes usar Nginx como proxy:
```bash
# Ejecutar con perfil nginx
docker-compose --profile nginx up -d
```

## 🔍 Solución de Problemas

### Problema: Advertencias de debconf durante la construcción
**Síntomas:** Mensajes como "debconf: unable to initialize frontend: Dialog" o "falling back to frontend: Noninteractive"

**Solución:** Estas advertencias son normales y no afectan el funcionamiento. El Dockerfile ya está configurado para manejarlas automáticamente con:
```dockerfile
ENV DEBIAN_FRONTEND=noninteractive
ENV DEBCONF_NONINTERACTIVE_SEEN=true
```

### Problema: Advertencia "version is obsolete" en docker-compose
**Síntomas:** Mensaje "the attribute `version` is obsolete, it will be ignored"

**Solución:** ✅ **Ya corregido** - La línea `version` ha sido eliminada del docker-compose.yml ya que es obsoleta en las versiones modernas de Docker Compose.

### Problema: La construcción se queda "estancada"
**Síntomas:** El proceso parece detenerse durante la instalación de paquetes

**Solución:** 
1. El proceso continúa en segundo plano, ten paciencia
2. Usa el script automatizado que proporciona mejor feedback
3. Si realmente se detiene, cancela con `Ctrl+C` y vuelve a intentar

### Problema: El contenedor no inicia
```bash
# Ver logs detallados
docker-compose logs app

# Reconstruir imagen
docker-compose down
docker-compose up -d --build --force-recreate
```

### Problema: Permisos de base de datos
```bash
# Acceder al contenedor y corregir permisos
docker-compose exec app bash
chown www-data:www-data /var/www/html/database/database.sqlite
chmod 664 /var/www/html/database/database.sqlite
```

### Problema: Assets no se cargan
```bash
# Recompilar assets dentro del contenedor
docker-compose exec app npm run build
```

## 📊 Monitoreo

### Ver uso de recursos
```bash
# Estadísticas del contenedor
docker stats grid-bot-helper

# Información del contenedor
docker inspect grid-bot-helper
```

### Backup de la base de datos
```bash
# Crear backup
docker-compose exec app cp /var/www/html/database/database.sqlite /var/www/html/database/backup-$(date +%Y%m%d).sqlite

# Copiar backup al host
docker cp grid-bot-helper:/var/www/html/database/backup-$(date +%Y%m%d).sqlite ./
```

## 🔄 Actualizaciones

### Actualizar la aplicación
```bash
# 1. Detener servicios
docker-compose down

# 2. Actualizar código
git pull origin main

# 3. Reconstruir y ejecutar
docker-compose up -d --build
```

## 🌍 Despliegue en Google Cloud

### Usando Google Cloud Run
```bash
# 1. Construir imagen para Cloud Run
docker build -t gcr.io/tu-proyecto/grid-bot-helper .

# 2. Subir imagen
docker push gcr.io/tu-proyecto/grid-bot-helper

# 3. Desplegar en Cloud Run
gcloud run deploy grid-bot-helper \
  --image gcr.io/tu-proyecto/grid-bot-helper \
  --platform managed \
  --region us-central1 \
  --allow-unauthenticated
```

### Usando Compute Engine
```bash
# 1. Conectar a la instancia
gcloud compute ssh tu-instancia

# 2. Instalar Docker
sudo apt update
sudo apt install docker.io docker-compose -y
sudo systemctl start docker
sudo systemctl enable docker

# 3. Clonar y ejecutar
git clone <tu-repositorio>
cd grid-bot-helper
sudo docker-compose up -d --build
```

## ✅ Ventajas del Despliegue con Docker

- ✅ **Sin problemas de permisos**: Todo se maneja dentro del contenedor
- ✅ **Entorno consistente**: Misma configuración en desarrollo y producción
- ✅ **Fácil escalabilidad**: Puedes ejecutar múltiples instancias
- ✅ **Actualizaciones simples**: Solo reconstruir la imagen
- ✅ **Aislamiento**: No afecta otros servicios del servidor
- ✅ **Portabilidad**: Funciona en cualquier servidor con Docker

## 📞 Soporte

Si encuentras problemas:
1. Revisa los logs: `docker-compose logs -f app`
2. Verifica que Docker esté ejecutándose: `docker --version`
3. Asegúrate de que los puertos no estén en uso: `netstat -tlnp | grep :80`
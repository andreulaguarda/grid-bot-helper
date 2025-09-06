#!/bin/bash

# Función para mostrar mensajes con formato
show_message() {
    echo -e "\n\033[1;34m===> $1\033[0m\n"
}

# Mostrar mensaje informativo sobre el proceso de construcción
show_message "Información importante sobre la construcción:"
echo "1. La primera construcción puede tardar varios minutos (instalación de dependencias)"
echo "2. Las reconstrucciones posteriores serán más rápidas gracias al caché de capas"
echo "3. Las advertencias de 'debconf' son normales y no afectan al funcionamiento"

# Generar certificados SSL si no existen
if [ ! -f "ssl/certificate.crt" ] || [ ! -f "ssl/private.key" ]; then
    show_message "Generando certificados SSL..."
    chmod +x ssl/generate-certs.sh
    ./ssl/generate-certs.sh
fi

# Limpiar contenedores anteriores pero mantener el caché
show_message "Deteniendo contenedores anteriores..."
docker compose down --remove-orphans

# Construir la nueva imagen
show_message "Construyendo la imagen con multi-stage builds..."
echo "Etapa 1/3: Construcción de assets de Node.js"
echo "Etapa 2/3: Instalación de dependencias de PHP"
echo "Etapa 3/3: Creación de imagen final optimizada"
docker compose build --progress=plain

# Iniciar los contenedores
show_message "Iniciando los contenedores..."
docker compose up -d

# Esperar a que la aplicación esté lista
show_message "Esperando a que la aplicación esté lista..."
sleep 10

# Verificar el estado de los contenedores
show_message "Verificando el estado de los contenedores..."
docker compose ps

# Mostrar la URL de acceso
show_message "¡Construcción completada! La aplicación está disponible en:"
echo "- HTTP:  http://localhost"
echo "- HTTPS: https://localhost (recomendado)"
echo "
Nota: Al acceder por HTTPS, verás una advertencia de seguridad por el certificado autofirmado.
Esto es normal en entornos de desarrollo."

# Mostrar comandos útiles
show_message "Comandos útiles:"
echo "- Ver logs: docker compose logs -f"
echo "- Detener contenedores: docker compose down"
echo "- Reiniciar contenedores: docker compose restart"
echo "- Reconstruir sin caché: docker compose build --no-cache"
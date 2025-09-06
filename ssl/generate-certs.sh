#!/bin/bash

# Crear directorio para los certificados si no existe
mkdir -p ssl
cd ssl

# Generar clave privada
openssl genrsa -out private.key 2048

# Generar certificado autofirmado
openssl req -x509 -new -nodes \
  -key private.key \
  -sha256 -days 365 \
  -out certificate.crt \
  -subj "/C=ES/ST=Madrid/L=Madrid/O=Grid Bot Helper/CN=gridbothelper.duckdns.org"

echo "Certificados SSL generados en el directorio ssl/:"
ls -l
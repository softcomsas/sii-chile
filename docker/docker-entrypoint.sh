#!/bin/bash
set -e

echo "🚀 Iniciando SII Chile API..."

# Ejecutar migraciones
echo "📦 Ejecutando migraciones de base de datos..."
if php yii migrate --interactive=0; then
    echo "✅ Migraciones completadas exitosamente"
else
    echo "❌ Error al ejecutar migraciones"
    exit 1
fi

echo "⏰ Iniciando servicio cron..."
service cron start

if [ $? -eq 0 ]; then
    echo "✅ Servicio cron iniciado correctamente"
else
    echo "❌ Error al iniciar servicio cron"
fi

echo "🌐 Iniciando servidor Apache..."

# Ejecutar el comando original de la imagen base
exec apache2-foreground

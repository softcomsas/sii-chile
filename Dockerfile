FROM yiisoftware/yii2-php:7.4-apache

WORKDIR /app

# Instalar dependencias del sistema (solo lo necesario)
RUN apt-get update && apt-get install -y curl \
    && rm -rf /var/lib/apt/lists/*

# Habilitar módulos de Apache
RUN a2enmod rewrite headers remoteip

# Copiar configuración de RemoteIP
COPY docker/apache/remoteip.conf /etc/apache2/conf-available/
RUN a2enconf remoteip

# Suprimir warning de ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copiar composer files y instalar dependencias
COPY composer.json composer.lock ./
RUN composer config --no-plugins allow-plugins.yiisoft/yii2-composer true \
    && composer install \
    --no-dev \
    --optimize-autoloader \
    --classmap-authoritative \
    && composer clear-cache

# Copiar el código de la aplicación
COPY . .

# Copiar configuración de producción desde environments
# (usa getenv() para leer variables de entorno en runtime)
COPY environments/prod/config/main-local.php config/main-local.php
COPY environments/prod/config/params-local.php config/params-local.php

# Copiar y configurar entrypoint
COPY docker/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Crear directorios y establecer permisos
RUN mkdir -p runtime web/assets upload \
    && chmod -R 777 runtime web/assets upload

# Variables de entorno
ENV YII_DEBUG=0 \
    YII_ENV=prod

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
FROM php:8.2-apache

# Actualizar el gestor de paquetes de Debian e instalar binarios de desarrollo necesarios
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libssl-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# Instalar y habilitar las extensiones de ejecución oficial de PHP
RUN docker-php-ext-install pdo pdo_pgsql

# Compilar el driver nativo de MongoDB mediante PECL e inyectarlo en el php.ini
RUN pecl install mongodb && docker-php-ext-enable mongodb

# Forzar el aislamiento y reescritura de cabeceras en Apache por seguridad perimetral
RUN a2enmod rewrite

# Mapear el código fuente al directorio raíz del servidor HTTP interno
COPY . /var/www/app/
RUN rm -rf /var/www/html/* && cp -r /var/www/app/public/* /var/www/html/

# Configurar Apache para que escuche en el puerto estándar asignado por Render
EXPOSE 80
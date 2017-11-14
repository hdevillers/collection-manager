FROM php:7.1.10-fpm

# To avoid a bug with the intl extension compilation
# PHP_CPPFLAGS are used by the docker-php-ext-* scripts
ENV PHP_CPPFLAGS="$PHP_CPPFLAGS -std=c++11"

# Install git, supervisor, yarn and libraries needed by php extensions
RUN apt-get update && \
    apt-get install -y \
            zlib1g-dev \
            git && \
    rm -rf /var/lib/apt/lists/*

# Compile ICU (required by intl php extension)
RUN curl -sS -o /tmp/icu.tar.gz -L http://download.icu-project.org/files/icu4c/59.1/icu4c-59_1-src.tgz && \
    tar -zxf /tmp/icu.tar.gz -C /tmp && \
    cd /tmp/icu/source && \
    ./configure --prefix=/usr/local && \
    make clean && \
    make && \
    make install

# Configure, install and enable php extensions
RUN docker-php-source extract && \
    docker-php-ext-configure intl --with-icu-dir=/usr/local && \
    docker-php-ext-install intl pdo pdo_mysql zip bcmath && \
    pecl install apcu-5.1.8 && \
    docker-php-ext-enable opcache apcu && \
    docker-php-source delete

# Install Composer
RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer && chmod +x /usr/local/bin/composer

# Copy the php.ini file
COPY ["./docker/php.ini", "./docker/php-cli.ini", "/usr/local/etc/php/"]

# Define the working directory
WORKDIR /var/www/html

# Copy the source code
COPY . /var/www/html/

# Install app dependencies
RUN composer install --no-suggest --optimize-autoloader && \
    chown -R www-data:www-data /var/www/html

# Copy script and supervisor conf
COPY ./docker/init.sh /opt/app/init.sh

# Clean installation (remove the Docker folder and empty the /tmp)
RUN rm -R ./docker /tmp/*

# Define the /var/www/html folder as volume
VOLUME /var/www/html
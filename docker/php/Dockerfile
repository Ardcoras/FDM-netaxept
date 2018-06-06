FROM fdmdev/php-7.1_xd_my:1.0.3

RUN apk add --no-cache freetype libpng libjpeg-turbo freetype-dev libpng-dev libjpeg-turbo-dev zlib-dev libmcrypt-dev libltdl && \
  docker-php-ext-configure gd \
    --with-gd \
    --with-freetype-dir=/usr/include/ \
    --with-png-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ && \
  NPROC=$(grep -c ^processor /proc/cpuinfo 2>/dev/null || 1) && \
  docker-php-ext-install -j${NPROC} gd && \
  apk del --no-cache freetype-dev libpng-dev libjpeg-turbo-dev

RUN docker-php-ext-configure mcrypt \
    && docker-php-ext-install \
		intl \
		zip \
		exif \
        mcrypt

RUN docker-php-ext-enable \
		opcache

RUN echo "date.timezone = Europe/Copenhagen" > /usr/local/etc/php/conf.d/timezone.ini
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memlimit.ini

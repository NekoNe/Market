FROM php:7.2-stretch

WORKDIR /app
COPY . /app

EXPOSE 8000


RUN apt-get -y update && apt-get install -y libpq-dev \
  && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
  && docker-php-ext-install pdo pdo_pgsql pgsql \
  && docker-php-ext-install mysqli && docker-php-ext-enable mysqli

CMD ["php", "-S", "0.0.0.0:8000", "router.php"]





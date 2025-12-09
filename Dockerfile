# PHP + FPM
FROM php:8.2-fpm-alpine

# Pacotes necessários
RUN apk add --no-cache nginx bash curl ca-certificates \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Diretórios
RUN mkdir -p /run/nginx

# Copia o código
WORKDIR /app
COPY . /app

# Nginx conf (template) e script de arranque
COPY .render/nginx.conf.template /etc/nginx/nginx.conf.template
COPY .render/start.sh /start.sh
RUN chmod +x /start.sh

# PHP-FPM tuning (opcional)
RUN { \
    echo '[www]'; \
    echo 'user = www-data'; \
    echo 'group = www-data'; \
    echo 'listen = 9000'; \
    echo 'pm = dynamic'; \
    echo 'pm.max_children = 10'; \
    echo 'pm.start_servers = 2'; \
    echo 'pm.min_spare_servers = 2'; \
    echo 'pm.max_spare_servers = 4'; \
  } > /usr/local/etc/php-fpm.d/zz-www.conf

# A Render define $PORT; vamos ler isso no start.sh
ENV APP_DOCROOT=/app

CMD ["/start.sh"]

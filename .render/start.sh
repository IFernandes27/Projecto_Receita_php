#!/usr/bin/env bash
set -e

# Render injeta $PORT; define padrão se não existir
export PORT="${PORT:-10000}"
export APP_DOCROOT="${APP_DOCROOT:-/app}"

# Renderiza o template do nginx com as variáveis atuais
envsubst '\$PORT \${APP_DOCROOT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Arranca PHP-FPM em background e Nginx em foreground
php-fpm -D
nginx -g "daemon off;"

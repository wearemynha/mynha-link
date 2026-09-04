#!/bin/sh

# Local preview only: reuse an initialized volume without synchronizing image
# files over the read-only templates, translations and assets from the workspace.
set -eu

cd /htdocs
if [ ! -f artisan ] || [ ! -f .env ]; then
    echo 'The installer preview requires an initialized application volume.' >&2
    exit 1
fi

export SERVER_ADMIN="${SERVER_ADMIN:-admin@example.com}"
export HTTP_SERVER_NAME="${HTTP_SERVER_NAME:-localhost}"
export HTTPS_SERVER_NAME="${HTTPS_SERVER_NAME:-localhost}"
export LOG_LEVEL="${LOG_LEVEL:-warn}"
export TZ="${TZ:-America/Sao_Paulo}"

printf '%s\n' \
    "memory_limit = ${PHP_MEMORY_LIMIT:-256M}" \
    "upload_max_filesize = ${UPLOAD_MAX_FILESIZE:-8M}" \
    "post_max_size = ${POST_MAX_SIZE:-16M}" \
    "date.timezone = ${TZ}" \
    'opcache.validate_timestamps = 1' \
    'opcache.revalidate_freq = 0' \
    > /etc/php83/conf.d/99-linkstack-runtime.ini

su-exec apache:apache php artisan view:clear --no-interaction
exec httpd -D FOREGROUND

FROM serversideup/php:8.4-fpm-nginx

# Laravel optimizations run automatically on container boot (config/route/view cache).
# NOTE: for apps where you run migrations MANUALLY on prod (your PureOps policy),
# set AUTORUN_ENABLED=false in the Coolify env panel — it overrides this line.
ENV AUTORUN_ENABLED=true
ENV WEB_DOCUMENT_ROOT=/var/www/html/public

# Copy application and set ownership
COPY --chown=www-data:www-data . /var/www/html

# Install PHP dependencies (production). vendor/ is .dockerignored so it builds fresh.
RUN composer install --no-dev --optimize-autoloader

# Writable dirs for Laravel
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Public-disk symlink (public/storage -> storage/app/public), baked at build time.
# Symlinks resolve at ACCESS time, so when Coolify mounts a persistent uploads
# volume at storage/app/public, this link points into the mount. Replaces
# `artisan storage:link`, which nothing in this image runs.
RUN ln -sfn /var/www/html/storage/app/public /var/www/html/public/storage

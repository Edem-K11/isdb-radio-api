#!/usr/bin/env bash
# Runtime prep for the Render container, then hand off to Apache.
set -e

# Render (and most PaaS) inject the port to listen on.
: "${PORT:=8080}"
sed -ri "s/^Listen [0-9]+/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" \
    /etc/apache2/sites-available/000-default.conf

# Rebuild the package manifest with the real environment available.
php artisan package:discover --ansi || true

# Schema is always brought up to date.
php artisan migrate --force

# Seed: creates the admin + demo content on first boot, syncs the admin
# credentials from ADMIN_* on every boot, never overwrites dashboard edits.
# Non-fatal: a transient failure here must not take the site down.
php artisan db:seed --force || echo "db:seed failed (non-fatal), will retry next deploy"

# Cache config/routes/views/Filament with the runtime environment baked in.
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:assets >/dev/null 2>&1 || true
php artisan filament:optimize >/dev/null 2>&1 || true

# Only useful when FILESYSTEM_DISK=public (local uploads). Harmless otherwise.
php artisan storage:link >/dev/null 2>&1 || true

exec "$@"

# Deployment

Target: Ubuntu Server 24.04 LTS (or Docker).

## Docker Compose (recommended baseline)

```bash
cp .env.example .env
# Set APP_KEY, APP_URL, DB_*, AI_* secrets
docker compose up -d --build
docker compose exec app php artisan migrate --force --seed
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

Services: `app` (php-fpm), `nginx`, `mysql:8`, `redis`, `worker` (`queue:work`).

Upload limits: `docker/php/php.ini` and `docker/nginx/default.conf` (`client_max_body_size 100M`).

## Bare-metal outline

1. Install Nginx, PHP 8.3-FPM, MySQL 8, Redis, Composer, Node.
2. Deploy code under `/var/www/...`, set ownership to the PHP-FPM user.
3. Point Nginx `root` to `public/`; pass PHP to php-fpm.
4. Configure Supervisor for `php artisan queue:work` (and optionally scheduler).
5. Enable HTTPS (Let’s Encrypt).
6. Set `APP_ENV=production`, `APP_DEBUG=false`, strong `APP_KEY`, and real DB/Redis credentials.
7. Run `php artisan migrate --force`, cache config/routes/views.
8. Restrict `storage/app/private` (submission files) — never expose via public web root.

## Queue & scheduler

- Worker: `php artisan queue:work redis --sleep=3 --tries=3`
- Scheduler (cron): `* * * * * cd /path && php artisan schedule:run`

## Health checks

- HTTP `/login` or `/dashboard` (auth)
- MySQL connectivity
- Redis ping
- Failed jobs table / Horizon-equivalent monitoring if added later

## Rollback

- Keep previous release directory + DB backup before migrate.
- Revert code symlink; restore DB dump if a migration is irreversible.

# Installation

## Requirements

- PHP 8.3+ with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `gd`, `zip`, `intl`, `redis` (recommended), `pcov` or `xdebug` (tests/coverage optional)
- Composer 2
- Node.js 20+ and npm
- MySQL 8.x
- Redis (recommended for queues/cache)

## Steps

1. Clone the repository and enter the project directory.
2. Copy environment file:

   ```bash
   cp .env.example .env
   ```

3. Install PHP dependencies:

   ```bash
   composer install
   ```

4. Generate application key:

   ```bash
   php artisan key:generate
   ```

5. Configure `.env`:

   - `DB_*` — MySQL connection
   - `QUEUE_CONNECTION=redis` (or `database`)
   - `REDIS_*` if using Redis
   - `AI_PROVIDER=null` for local demo without API keys
   - Optional Google OAuth: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`

6. Run migrations and demo seed:

   ```bash
   php artisan migrate --seed
   ```

7. Build frontend assets:

   ```bash
   npm install
   npm run build
   ```

8. Link public storage (if using public disk) and ensure private storage exists:

   ```bash
   php artisan storage:link
   mkdir -p storage/app/private
   ```

9. Start services:

   ```bash
   php artisan serve
   php artisan queue:work
   ```

## Docker alternative

See [DEPLOYMENT.md](DEPLOYMENT.md) and `docker compose up -d --build`.

## Demo logins

- Admin: `admin@example.com` / `password`
- Lecturer: `demo@academic.test` / `password`

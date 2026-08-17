# AI Academic Assessment Platform

Web application for lecturers to manage courses, rubrics, question banks, and AI-assisted grading of student submissions. **AI suggests scores; lecturers always control the final mark.**

## Stack

- Laravel 13 (PHP 8.3)
- Livewire + Blade + Tailwind
- MySQL 8, Redis, Laravel Queue
- AI providers: OpenAI, Gemini, Ollama, or deterministic `null` mock

## Quick start (local)

```bash
cp .env.example .env
composer install
php artisan key:generate
# Configure DB_* in .env, then:
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Demo accounts (from `DemoSeeder`):

| Role     | Email                 | Password |
|----------|-----------------------|----------|
| Admin    | admin@example.com     | password |
| Lecturer | demo@academic.test    | password |

## Docker

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
```

App: `http://localhost:8080` (see `APP_PORT`).

## Documentation

| Doc | Topic |
|-----|--------|
| [USER_MANUAL.md](USER_MANUAL.md) | **Manual penggunaan (dosen & admin)** |
| [INSTALLATION.md](INSTALLATION.md) | Local setup |
| [DEPLOYMENT.md](DEPLOYMENT.md) | Production / Ubuntu |
| [AI_CONFIGURATION.md](AI_CONFIGURATION.md) | Providers & prompts |
| [DATABASE.md](DATABASE.md) | Schema overview |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Modules & flows |
| [API.md](API.md) | Web-first MVP / future API |
| [TESTING.md](TESTING.md) | PHPUnit |
| [SECURITY.md](SECURITY.md) | Auth, files, audit |

## Principle

> AI is an assistant in the assessment process, not the final decision maker.

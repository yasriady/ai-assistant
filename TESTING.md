# Testing

## Tooling

- PHPUnit 12 (`php artisan test`)
- `RefreshDatabase` on DB-backed tests
- SQLite in-memory via `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Requires PHP extension `pdo_sqlite`.

## Suites

**Unit**

- `RubricScoringServiceTest`
- `DeterministicGradingServiceTest`
- `AIResponseValidatorTest`
- `StudentFilenameParserTest`

**Feature** (authenticate as lecturer)

- `AuthLoginTest`
- `CourseManagementTest`
- `AssessmentReviewTest`

## Run

```bash
composer install
php artisan test
# or
./vendor/bin/phpunit
```

Filter:

```bash
php artisan test --filter=RubricScoringServiceTest
```

## Notes

- Feature tests use Livewire’s `Livewire::test()` for create/review flows.
- Queue is `sync` in testing.
- Do not point tests at production MySQL; prefer `:memory:` SQLite.

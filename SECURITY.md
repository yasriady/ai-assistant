# Security

## Authentication

- Email/password login (`LoginController`)
- Optional Google OAuth (Socialite) when `GOOGLE_CLIENT_*` is set
- Roles: `admin`, `lecturer` (`UserRole` + `EnsureUserHasRole`)

## Authorization

- Policies on courses, assessments, rubrics, submissions, question banks
- Lecturers only access their own resources; admins can bypass

## Files

- Submission files stored on **private** disk (`storage/app/private`)
- Downloads via **signed** routes only (`files.download`)
- Upload size limits enforced in PHP/Nginx config

## AI & grading integrity

- AI scores are suggestions until lecturer review/finalize
- Score changes and finalize actions written to `audit_logs`
- Prompt templates discourage hallucinated evidence

## Secrets

- Never commit `.env` (see `.gitignore`)
- Rotate `APP_KEY`, DB passwords, and AI API keys in production
- Prefer Redis/database sessions over cookie-only secrets exposure

## Hardening checklist

- `APP_DEBUG=false` in production
- HTTPS termination
- CSRF on web forms (Laravel default)
- Rate-limit login if exposed publicly
- Keep `storage/` and `.env` outside web root exposure
- Regular dependency updates (`composer audit`)

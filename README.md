# Medical Profiling System

Laravel clinic profiling app with role-based encoding, dynamic form fields, spreadsheet editing, autosave, and Excel export.

## Roles

- **Super Admin** — stations, form builder, users, spreadsheet, export, encode
- **Admin** — spreadsheet, export, encode, soft-delete clients
- **Encoder** — create/search clients, encode assigned working station (other stations read-only)

## Setup

```bash
composer install
cp .env.example .env   # if needed
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`.

## Seeded logins

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@clinic.test | password |
| Admin | admin@clinic.test | password |
| Encoder | encoder@clinic.test | password |

## Main features

- System-generated client IDs (`MED-YYYYMMDD-0001`)
- **Visit history** — each checkup is a visit; older visits stay unchanged
- New visit copies only: last name, first name, DOB, sex, client type
- Global form with seeded core fields (editable in form builder)
- Station-based encoding with field-level autosave
- Conflict UI (keep theirs / overwrite)
- Spreadsheet grid shows **latest visit only** (admin/super admin)
- Excel export for admin/super admin
- Soft delete clients (admin/super admin only)

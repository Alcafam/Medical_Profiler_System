# Medical Profiling System

Laravel clinic profiling app with role-based encoding, dynamic form fields, spreadsheet editing, autosave, Excel export, and medicine inventory.

## Roles

- **Super Admin** — stations, form builder, users, spreadsheet, export, encode, medicine inventory
- **Admin** — spreadsheet, export, encode, soft-delete clients, medicine inventory
- **Encoder** — create/search clients, encode visits (opens on assigned station); Consultation encoders see the consultation queue instead of the full client list; Consultation can recommend medicines; Pharmacy dispenses from inventory

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

Password for all: `12345678`

| Role | Email | Station |
|------|-------|---------|
| Super Admin | sadmin@mail.com | — |
| Admin | admin@mail.com | — |
| Encoder | encoder.registration@mail.com | Registration |
| Encoder | encoder.vitals@mail.com | Vitals |
| Encoder | encoder.blood.glucose@mail.com | Blood Glucose |
| Encoder | encoder.consultation@mail.com | Consultation |
| Encoder | encoder.pharmacy@mail.com | Pharmacy |

## Main features

- System-generated client IDs (`MED-YYYYMMDD-0001`)
- **Visit history** — each checkup is a visit; older visits stay unchanged
- New visit copies only: last name, first name, DOB, sex, client type
- Global form with seeded core fields (editable in form builder)
- Station-based encoding with field-level autosave
- **Consultation queue** — Blood Glucose can send patients to consult; consultation encoders see an Active/Completed queue
- **Medicine inventory** — admin/super admin CRUD with expiry row colors and archive; seeded from mission inventory CSV
- **Prescription** — Consultation picks from inventory (no stock change); out-of-stock and expired meds are hidden
- **Dispense** — Pharmacy picks from inventory and updates QTY Dispensed / Remaining
- Conflict UI (keep theirs / overwrite)
- Spreadsheet grid shows **latest visit only** (admin/super admin)
- Excel export for admin/super admin
- Soft delete clients (admin/super admin only)

# JusticeHub CRM

A comprehensive Case & Referral Management System built for legal aid hubs operating across Sindh, Pakistan. Developed for the Legal Aid Society (LAS) to digitize and streamline justice delivery services at the grassroots level.

---

## Overview

JusticeHub CRM enables justice hub coordinators, lawyers, mediators, and administrators to manage client intake, case referrals, service delivery, SLA compliance, and impact reporting — all from a single platform.

The system supports multiple justice hubs across Sindh with role-based access control, ensuring each hub operates independently while allowing central oversight by program management.

---

## Key Features

### Case & Intake Management
- Multi-step intake form capturing client demographics, location, legal issue, and service pathway
- Auto-assignment of cases to lawyers, mediators, or hub coordinators based on pathway
- Unique case ID generation per intake
- Support for walk-in, referral, and returning clients
- Full Sindh location cascade — District → Tehsil/Taluka/Town → Union Council (2,287 records from official dataset)

### Service Pathways
- Free Legal Advice / Consultation
- Mediation & ADR / Dispute Resolution
- Representation in Court (Litigation)
- NADRA & Documentation (CNIC, FRC, BISP)
- Government Department / Public Institution referrals
- Civil Society / NGO / CSO referrals
- Information & Awareness sessions

### Role-Based Access Control
- **Super Admin** — full system access across all hubs
- **Program Manager** — cross-hub oversight and reporting
- **Hub Coordinator** — manages cases within their hub, marks resolution
- **Lawyer** — views and manages cases assigned to them
- **Mediator** — manages ADR cases assigned to them
- **Court Clerk** — manages litigation cases and court calendars
- **Data Entry** — intake and data entry only

### SLA Compliance Tracking
- Dynamic SLA deadlines based on case urgency (Immediate / High / Medium / Low)
- Real-time SLA status on every case (Met / Pending / Breach)
- Automatic hourly SLA breach notifications via in-app bell and email
- SLA column in case list with visual pill badges

### Notifications System
- In-app bell notification with live badge counter
- Notifications for: case assigned, case updated, case approved, case rejected, case resolved, SLA breach approaching
- Email notifications via SMTP (configurable)
- Mark individual or all notifications as read

### Case Reassignment & Transfer
- Transfer cases between staff with mandatory reason, date, and approval trail
- Full transfer history per case
- Pending transfer banner with approve/reject actions
- Audit log of all transfers

### Dashboards & Reporting
- Command Center dashboard for program management
- Litigation & ADR dashboard for court and mediation tracking
- KPI cards, cohort filters, disposition breakdown
- Service pathway counts with click-to-filter
- Impact report with PDF export

### Settings & Administration
- Lookup management for all dropdown values
- Hub management with district assignment
- User management with role and hub assignment
- Location management (District / Taluka / Union Council)
- Staff directory with designation and staff UID

---

## Tech Stack

- **Backend:** Laravel 11 (PHP 8.2)
- **Frontend:** Blade templates, Vanilla JS, Vite
- **Database:** MySQL
- **Auth:** Laravel Breeze with role-based middleware
- **Permissions:** Spatie Laravel Permission
- **Notifications:** Laravel Notifications (database + mail channels)
- **File Processing:** PhpSpreadsheet (Excel import)
- **Activity Log:** Spatie Activity Log

---

## Installation

```bash
# Clone the repository
git clone https://github.com/irfannawaz-dev/justicehub.git
cd justicehub

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env, then run migrations
php artisan migrate

# Seed initial data
php artisan db:seed

# Import Sindh location data (requires MasterReferenceDatasetforSindh.xlsx in project root)
php artisan locations:import-sindh --fresh

# Build frontend assets
npm run build

# Start the development server
php artisan serve
```

---

## Environment Variables

Key `.env` values to configure:

```
APP_NAME=JusticeHub
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=justicehub
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=your@gmail.com
MAIL_FROM_NAME="JusticeHub"
```

---

## Scheduled Tasks

Add this to your server cron for SLA breach notifications and CMS draft resolution (every 15 minutes):

```
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

## LAS CMS litigation integration

Only `Representation in Court` / `Court Representation` cases use this API. Configure `LAS_CMS_API_URL` and `LAS_CMS_API_KEY` on the server.

- Existing cases: `GET /cases/lookup?cnic=...` matches a valid 13-digit CNIC (formatting differences are ignored). Names do not need to match. Multiple programs with the same CNIC require manual selection; the selected program's CNIC is checked again.
- New intakes: `POST /cases` saves `interview_id` as a draft. `GET /cases/source/{justicehub_case_uid}` resolves that draft to `program_id` after conversion in CMS. An interview ID is never used as a program ID.
- Case details, hearings, timeline and litigation board data come from the linked program's API endpoints. Connected counts include verified court programs and pending CMS drafts; other service pathways are excluded.

Recheck existing court cases after deploying these changes:

```bash
php artisan las:reconcile-links --all --dry-run
php artisan las:reconcile-links --all
```

`las:sync-drafts` can also be run manually. `las:sync-hearings` imports hearings and updates linked case information. The reconciliation command only reads the CMS API; it does not create interviews or programs. The old `--try-name` / `--name-fallback` options remain accepted for script compatibility but never permit name-only links.

---

## License

Proprietary — Legal Aid Society (LAS), Sindh, Pakistan. All rights reserved.

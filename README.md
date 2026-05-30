# ACES Point — Instructors Calendar

A fully functional rebuild of the instructors calendar at `point.acesint.org/instructors-calendar`.
Laravel 12 backend + Blade/Tailwind frontend with a FullCalendar-driven, login-gated
scheduling UI in a futuristic glassmorphism theme.

> This is a clean **functional rebuild**, not a byte-for-byte copy. The original site is
> login-gated with its backend + database on the owner's server, so its exact data model
> and visuals couldn't be observed. The schema below is a sensible reconstruction — import
> your real data into it when ready.
>
> [![Laravel](https://github.com/SimonTingle/cursos-y-diplomas/actions/workflows/laravel.yml/badge.svg)](https://github.com/SimonTingle/cursos-y-diplomas/actions/workflows/laravel.yml)

## Stack

- **Backend:** Laravel 12 (PHP 8.2), session auth via Laravel Breeze (Blade) with admin/instructor roles
- **Frontend:** Blade + Tailwind CSS v3 + Alpine.js, bundled with Vite
- **Calendar:** FullCalendar (dayGrid / timeGrid / interaction)
- **Database:** SQLite by default (swap to MySQL for production — see below)

## Features

- **Public landing** (`/` for guests) — course info box + Sign in / Sign up / Change
  password buttons
- **Signed-in portal** (`/portal`, the post-login home) — intro box, this month's events
  with prev/next navigation, and a past-event photo gallery
- **Portal sections** (left nav) — Courses (enrollable), Video library (embedded YouTube),
  PDF catalogue, and an admin-managed Image gallery (see below)
- **Roles:** `admin` (full control) and `instructor` (read-only calendar)
- `/instructors-calendar` — month / week / day calendar views
- Create / edit / delete sessions via a modal (admins only)
- Drag-and-drop to reschedule and resize events (admins only)
- Filter the calendar by instructor
- Per-event and per-instructor accent colors
- **Automatic language detection** — English & Spanish (see below)

## Languages (i18n)

The UI auto-detects the visitor's language from the browser's `Accept-Language` header
and renders accordingly. Resolution order (in [`SetLocale`](app/Http/Middleware/SetLocale.php)):

1. `?lang=` query override (e.g. `/login?lang=es`)
2. the locale already saved in the session
3. the browser `Accept-Language` header
4. the app fallback (`config('app.locale')`)

The chosen locale is persisted in the session, and there's a language switcher in the
account menu (and on the login page). Translations live in `lang/*.json`
(`lang/es.json` ships Spanish; English is the source text). FullCalendar is localized too
via its locale bundles in [`resources/js/calendar.js`](resources/js/calendar.js).

**Add a language:** create `lang/<code>.json`, add `<code>` to
`SetLocale::SUPPORTED`, add the switcher option, and (optionally) import its FullCalendar
locale bundle.

## Portal & past-event photos

After login, users land on `/portal` ([`PortalController`](app/Http/Controllers/PortalController.php)),
which shows an intro box, the current month's sessions (with prev/next month navigation
via `?month=YYYY-MM`), and a past-event photo gallery. The full calendar is one click away.

The gallery reads image files directly from **`public/images/portal-gallery/`** — it ships
with placeholder SVG tiles. Drop real `.jpg` / `.png` / `.webp` files in that folder and
they appear automatically (sorted by filename); remove the placeholders when you do.

## Portal content sections

The portal's left nav links to four admin-managed sections (separate pages under
`/portal/...`). Any logged-in user can view them; only **admins** see add/delete controls
(write routes are gated with `can:admin`). Uploads use the `public` disk — run
`php artisan storage:link` once.

| Section | URL | Admin | Users |
|---------|-----|-------|-------|
| Cursos | `/portal/cursos` | add / delete courses, see enrollee counts | **Inscribirse** (enroll) / cancel |
| Videoteca | `/portal/videoteca` | add / delete YouTube links | watch embedded players |
| PDF | `/portal/pdf` | upload / delete PDFs | open / download |
| Galería de imágenes | `/portal/galeria` | upload / delete images | view grid |

Data model: `courses` + `enrollments` (pivot to `users`), `videos`, `pdfs`,
`gallery_images`. This admin-managed gallery is **separate** from the portal-home
placeholder gallery above. PDFs are stored in `storage/app/public/pdfs`, images in
`storage/app/public/gallery`, and deleting a record removes its file.

## Admin login

The admin account is created by a seeder from env vars (`.env`):

```dotenv
ADMIN_NAME="Administrator"
ADMIN_EMAIL=admin@acespoint.test
ADMIN_PASSWORD=password        # ⚠️ change this before any non-local use
```

Create / refresh the admin:

```bash
php artisan db:seed --class=AdminUserSeeder
```

Log in at `/login` with those credentials. Admins see a **New session** button and can
create/edit/move/delete events; anyone who self-registers at `/register` is an
`instructor` (role `instructor`) and gets a **read-only** calendar — write API routes are
gated server-side with `can:admin` and return `403` for non-admins. Promote a user to
admin by setting their `role` column to `admin`.

## Getting started

```bash
# 1. PHP deps (Composer is bundled locally as ./composer, or use a global composer)
./composer install

# 2. App key + database
cp .env.example .env        # already present; edit if needed
php artisan key:generate
touch database/database.sqlite
php artisan migrate

# 3. Frontend assets
npm install
npm run build               # or: npm run dev  (hot reload)

# 4. Run
php artisan serve
```

Then open the served URL. There are **no seeded accounts** (empty schema by design) —
register the first account at `/register`, then you'll land on the calendar.

## Data model

- **instructors** — `name`, `email`, `phone`, `title`, `bio`, `avatar`, `color`, `is_active`
- **events** — `title`, `description`, `instructor_id`, `start_at`, `end_at`, `all_day`,
  `location`, `color`, `status` (`scheduled` / `completed` / `cancelled`)
- **users** — Breeze authentication

## Routes

| Method | URI | Purpose |
|--------|-----|---------|
| GET | `/instructors-calendar` | Calendar page (auth) |
| GET | `/events` | FullCalendar feed — `?start=&end=&instructor_id=` |
| POST | `/events` | Create a session |
| PUT | `/events/{event}` | Update / reschedule |
| DELETE | `/events/{event}` | Delete |
| GET | `/instructors` | Instructor list (filter/legend) |
| POST | `/instructors` | Create an instructor |

## Switching to MySQL (production)

Edit `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=acespoint
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

Then run `php artisan migrate`. The schema is database-agnostic and compatible with the
existing Laravel/Cloudflare hosting.

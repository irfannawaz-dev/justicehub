# Justice Hub CRM — Security Audit Report

**Audit Date:** 2026-05-19  
**Application:** Justice Hub Laravel 12 (c:\xampp\htdocs\JUSTICEHUB\JusticeHubLaravel)  
**Framework:** Laravel 12 / PHP 8.2 / MariaDB  
**Status:** Pre-production review — fixes required before deployment

---

## Summary

| Severity | Count | Status |
|----------|-------|--------|
| Critical | 1 | ❌ Open |
| High | 10 | ❌ Open |
| Medium | 7 | ❌ Open |
| Low | 4 | ⚠ Noted |
| **Total** | **22** | |

**Overall Risk Level: HIGH** — Do not deploy to production until Critical and High findings are resolved.

---

## Positive Security Practices Found

- All forms include `@csrf` token — CSRF protection active
- All models use `$fillable` arrays — mass assignment protected
- Eloquent ORM used throughout — no SQL injection risk
- Spatie `laravel-permission` integrated — RBAC in place
- Passwords hashed with bcrypt (12 rounds)
- Session driver: database (not cookies)
- `session.http_only = true` — JS cannot access session cookie
- `session.same_site = lax` — CSRF protection for cross-origin requests
- `DB::raw()` used only with hardcoded strings — no user input interpolation

---

## CRITICAL FINDINGS

### C1 — APP_DEBUG Enabled
- **File:** `.env` line 4
- **Issue:** `APP_DEBUG=true` exposes full stack traces, environment variables, database queries, and source code paths in error pages.
- **Impact:** Any unhandled exception leaks internal architecture details to attackers.
- **Fix:** Set `APP_DEBUG=false` in `.env` before any production deployment. Use `LOG_LEVEL=error` for server-side logging.

---

## HIGH FINDINGS

### H1 — Missing Authorization on Case Approve / Reject / Resolve
- **File:** `app/Http/Controllers/CaseController.php` lines 83–112
- **Methods:** `approve()`, `reject()`, `resolve()`
- **Issue:** No `$this->authorize()` or `can:` middleware. Any authenticated user (including Viewer, Data Entry) can approve, reject, or resolve any case.
- **Impact:** Privilege escalation — unauthorized users can manipulate case approval workflow.
- **Fix:**
  ```php
  public function approve(Request $request, CaseRecord $case)
  {
      abort_unless($request->user()->can('cases.approve'), 403);
      // ...
  }
  ```
  Or apply on route: `->middleware('can:cases.approve')`

### H2 — Missing Authorization on Staff Training Log
- **File:** `app/Http/Controllers/StaffController.php` lines 73–105
- **Method:** `logTraining()`
- **Issue:** Any authenticated user can log training records for any staff member.
- **Impact:** Compliance records can be falsified by unauthorized users.
- **Fix:**
  ```php
  abort_unless($request->user()->can('staff.training.log'), 403);
  ```

### H3 — Missing Authorization on Complaint Action / Status Change
- **File:** `app/Http/Controllers/ComplaintController.php` lines 81–101
- **Method:** `addAction()`
- **Issue:** Any authenticated user can add actions and change complaint status (resolve, escalate).
- **Impact:** Unauthorized users can close or escalate complaints they have no authority over.
- **Fix:**
  ```php
  abort_unless($request->user()->can('complaints.resolve'), 403);
  ```

### H4 — Missing Authorization on Evidence Verification
- **File:** `app/Http/Controllers/EvidenceController.php` lines 71–80
- **Method:** `verify()`
- **Issue:** Any authenticated user can mark evidence as verified. Only M&E Lead and Head should be able to verify.
- **Impact:** Unqualified staff can verify/certify evidence, compromising report integrity.
- **Fix:**
  ```php
  abort_unless($request->user()->can('evidence.verify'), 403);
  ```

### H5 — Session Encryption Disabled
- **File:** `.env` line 32
- **Issue:** `SESSION_ENCRYPT=false` — session payload stored unencrypted in the `sessions` database table.
- **Impact:** If DB is compromised, all session data (hub, user role, active flags) is readable.
- **Fix:** Set `SESSION_ENCRYPT=true` in `.env`.

### H6 — Session Secure Cookie Not Configured
- **File:** `.env` lines 30–34
- **Issue:** `SESSION_SECURE_COOKIE` is not set. Defaults to `false`, allowing session cookies over plain HTTP.
- **Impact:** Session hijacking via network interception on non-HTTPS connections.
- **Fix:**
  - Development: `SESSION_SECURE_COOKIE=false`
  - Production: `SESSION_SECURE_COOKIE=true` (enforce HTTPS)

### H7 — No Rate Limiting on API Search Endpoint
- **File:** `routes/api.php` line 16
- **Issue:** `GET /api/search` has no throttle middleware. Attacker can enumerate case records by iterating search terms.
- **Impact:** PII enumeration — client names, CNICs, case details can be scraped.
- **Fix:**
  ```php
  Route::get('/search', [SearchController::class, 'search'])
      ->middleware('throttle:30,1');
  ```

### H8 — No Rate Limiting on API Lookups Endpoint
- **File:** `routes/api.php` line 17
- **Issue:** `GET /api/lookups/{group}` has no throttle middleware.
- **Impact:** Unrestricted enumeration of all lookup groups and values.
- **Fix:**
  ```php
  Route::get('/lookups/{group}', [LookupController::class, 'index'])
      ->middleware('throttle:60,1');
  ```

### H9 — CNIC Stored as Plaintext
- **File:** `app/Models/CaseRecord.php` line 26 (`$fillable`)  
- **Table:** `cases.cnic` column
- **Issue:** National ID card numbers are stored and retrieved without encryption.
- **Impact:** Full PII exposure if database is compromised. Potentially violates data protection obligations.
- **Fix:** Use Laravel's built-in encrypted cast:
  ```php
  protected $casts = [
      'cnic' => 'encrypted',
  ];
  ```
  Data will be transparently encrypted on write and decrypted on read using `APP_KEY`.

### H10 — No Input Validation on Settings Routes
- **File:** `app/Http/Controllers/SettingsController.php` lines 15–33
- **Methods:** `setHub()`, `setTheme()`
- **Issue:** `hub_id` and `theme` are written directly to session without validation. Invalid or crafted values can corrupt session state.
- **Fix:**
  ```php
  // setHub
  $request->validate([
      'hub_id' => ['required', 'string', Rule::in(
          Hub::pluck('id')->push('all')->toArray()
      )],
  ]);

  // setTheme
  $request->validate([
      'theme' => ['required', Rule::in(['light', 'dark'])],
  ]);
  ```

---

## MEDIUM FINDINGS

### M1 — Missing Authorization on Reflection / Case Study Creation
- **File:** `app/Http/Controllers/LearningController.php` lines 47–88
- **Methods:** `storeReflection()`, `storeCaseStudy()`
- **Issue:** No explicit authorization check. Any authenticated user can create learning records.
- **Fix:** Add `abort_unless($request->user()->can('cases.edit'), 403);`

### M2 — Case ID Not Validated Against User's Hub in Feedback
- **File:** `app/Http/Controllers/FeedbackController.php` lines 72–91
- **Method:** `store()`
- **Issue:** `case_id` from request is used with `CaseRecord::find()` without confirming the case belongs to the user's hub. Cross-hub feedback linking is possible.
- **Fix:** Add to validation rules:
  ```php
  'case_id' => 'nullable|exists:cases,id',
  ```
  Then verify hub scope: `CaseRecord::forAuthUser()->find($request->case_id)`

### M3 — Missing Authorization on Impact Report Export
- **File:** `app/Http/Controllers/ImpactController.php` lines 90–106
- **Method:** `export()`
- **Issue:** Any authenticated user can export full impact reports containing aggregated PII and financial data.
- **Fix:** `abort_unless($request->user()->can('reports.export'), 403);`

### M4 — Missing Authorization on Outreach Activity Creation
- **File:** `app/Http/Controllers/OutreachController.php` lines 32–63
- **Method:** `store()`
- **Issue:** Any authenticated user can create outreach records for any hub.
- **Fix:** `abort_unless($request->user()->can('outreach.create'), 403);`

### M5 — Complaint Severity Not Whitelisted in Validation
- **File:** `app/Http/Controllers/ComplaintController.php` lines 52–53
- **Issue:** `severity` field drives SLA day calculation but is not validated against an enum whitelist. Invalid values silently default to 14 days.
- **Fix:**
  ```php
  'severity' => ['required', Rule::in(['critical', 'high', 'medium', 'low'])],
  ```

### M6 — Training Expiry Date Not Validated
- **File:** `app/Http/Controllers/StaffController.php` lines 84–91
- **Issue:** `expires` date is accepted without verifying it is after `completed_on`. Past expiry dates create invalid compliance records.
- **Fix:**
  ```php
  'expires' => 'nullable|date|after:completed_on',
  ```

### M7 — Impact Export Parameters Not Whitelisted
- **File:** `app/Http/Controllers/ImpactController.php` lines 90–106
- **Issue:** `period` and `template` values are used in PDF filename without whitelisting. Low direct risk but inconsistent with validation standards.
- **Fix:**
  ```php
  $request->validate([
      'period'   => ['required', Rule::in(['Q1','Q2','Q3','Q4','H1','H2','Annual','Custom'])],
      'template' => ['required', Rule::in(['program-overview','annual-impact','donor-report','policy-brief','case-study-collection'])],
  ]);
  ```

---

## LOW FINDINGS

### L1 — Unescaped Chart Data via `{!! !!}` (Safe but fragile)
- **Files:** `resources/views/dashboards/command-center.blade.php` lines 54, 62, 70, 90, 98, 212  
  `resources/views/dashboards/lcd.blade.php` lines 60, 72
- **Issue:** Chart data injected via `{!! json_encode($data) !!}` into Alpine.js `x-data` attributes. Currently safe — all values are DB aggregates (counts, labels) not user-controlled.
- **Risk:** Low. Would become XSS if user-supplied labels (e.g. case categories from `meta` JSON) were injected directly without encoding.
- **Recommendation:** Replace with Laravel's `Js::from()` helper for explicit safety:
  ```blade
  <div x-data="serviceMixPie({{ Illuminate\Support\Js::from($chartServiceMix) }})">
  ```

### L2 — Hardcoded Demo Date in Staff and Impact Controllers
- **File:** `app/Http/Controllers/StaffController.php` line 21  
  `app/Http/Controllers/ImpactController.php` line 37
- **Issue:** `Carbon::parse('2026-04-29')` hardcoded as "today" for SLA and compliance calculations. Left over from demo dataset pinning.
- **Impact:** Incorrect compliance reporting in production from day 1.
- **Fix:** Replace with `now()` in both files before production.

### L3 — No Pagination Input Validation
- **Files:** `CaseController.php`, `ComplaintController.php`, `EvidenceController.php`, `FeedbackController.php`
- **Issue:** `page` query parameter is not validated (Laravel's `paginate()` handles it safely, but no explicit constraint).
- **Risk:** Very low — Laravel's paginator is safe. Noted for completeness.

### L4 — No HTTP Security Headers
- **Issue:** No middleware sets `X-Frame-Options`, `X-Content-Type-Options`, `Content-Security-Policy`, or `Strict-Transport-Security` headers.
- **Impact:** Clickjacking and MIME-type sniffing attacks possible.
- **Fix:** Add to `bootstrap/app.php`:
  ```php
  ->withMiddleware(function (Middleware $middleware) {
      $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
  })
  ```
  Or use `fruitcake/laravel-cors` / helmet-equivalent package.

---

## Prioritized Remediation Plan

### Immediate — Before Any Production Use
1. **C1** — Set `APP_DEBUG=false` in `.env`
2. **H1–H4** — Add `abort_unless(can(...), 403)` to approve, logTraining, addAction, verify
3. **H7–H8** — Add `throttle:30,1` and `throttle:60,1` to API routes
4. **H5** — Set `SESSION_ENCRYPT=true`
5. **H9** — Add `'cnic' => 'encrypted'` cast to CaseRecord model
6. **L2** — Replace `Carbon::parse('2026-04-29')` with `now()` in StaffController and ImpactController

### Short Term — Within 1 Week
7. **H6** — Set `SESSION_SECURE_COOKIE=true` for production `.env`
8. **H10** — Add `Rule::in()` validation to `setHub()` and `setTheme()`
9. **M1–M4** — Add `abort_unless(can(...))` to LearningController, FeedbackController, ImpactController, OutreachController
10. **M5–M7** — Add whitelist validation for severity, expiry, and export params

### Medium Term — Before Public/Donor Access
11. **L1** — Migrate chart injections to `Js::from()` helper
12. **L4** — Implement HTTP Security Headers middleware
13. Add comprehensive authorization tests for all 6 roles
14. Add audit logging for sensitive operations (CNIC access, case approval, evidence verification)

---

## Sign-off Checklist (for Production Readiness)

- [ ] C1: `APP_DEBUG=false` — **set manually in .env before deploying**
- [x] H1: Case approve/reject/resolve authorized — `abort_unless(can('cases.approve'))` added
- [x] H2: logTraining authorized — `abort_unless(can('staff.training.log'))` added
- [x] H3: addAction authorized — `abort_unless(can('complaints.resolve'))` added
- [x] H4: verify authorized — `abort_unless(can('evidence.verify'))` added
- [x] H5: `SESSION_ENCRYPT=true` — default changed in `config/session.php`
- [ ] H6: `SESSION_SECURE_COOKIE=true` — **set manually in .env on production HTTPS server**
- [x] H7: Rate limiting on `/api/search` — `throttle:30,1` applied
- [x] H8: Rate limiting on `/api/lookups` — `throttle:60,1` applied
- [x] H9: CNIC encrypted at rest — `'cnic' => 'encrypted'` cast added to CaseRecord
- [x] H10: Settings input validated — `Rule::in()` on hub_id and theme
- [x] M1: storeReflection/storeCaseStudy authorized
- [x] M2: case_id validated against user's hub scope in FeedbackController
- [x] M3: Impact export authorized — `abort_unless(can('reports.export'))` added
- [x] M4: Outreach store authorized — `abort_unless(can('outreach.create'))` added
- [x] M5: Complaint severity whitelisted — `Rule::in(['critical','high','medium','low'])`
- [x] M6: Training expiry validated — `after:completed_on` rule added
- [x] M7: Export params whitelisted — `Rule::in()` on period and template
- [x] L2: Hardcoded dates removed — `now()` used in StaffController and ImpactController
- [x] L4: Security headers implemented — `SecurityHeaders` middleware registered globally

**Remaining manual production steps:**
1. Set `APP_DEBUG=false` in `.env`
2. Set `SESSION_SECURE_COOKIE=true` in `.env` (production only, requires HTTPS)
3. Re-seed database after CNIC encryption cast: `php artisan migrate:fresh --seed`

L5 — Data Transfer (Manual Entry)

Overview:
This file documents the manual measurement entry feature added for User Story L5.

Files added:
- `manual_entry.php` — web page for authenticated users to manually enter measurements.

How to use:
1. Login to the web application.
2. Visit `/manual_entry.php`.
3. Fill in fields: Station Serial, Temperature, Humidity, Pressure, Light, Gas. Timestamp is optional (ISO 8601). If left empty, server uses current UTC time.
4. Submit. The page performs client-side validation and server-side validation. On success, the record is inserted into the `measurement` table.

Server-side validation rules (must / acceptance):
- `station_serial`: required, pattern `[A-Za-z0-9_\-]{1,64}` and must exist in `station` table.
- `temperature`: float, range -50 .. 60 °C
- `humidity`: float, range 0 .. 100 %
- `pressure`: float, range 300 .. 1100 hPa
- `light`: float, range 0 .. 100000 lux
- `gas`: float, range 0 .. 10000

Developer notes / annotated code:
- `manual_entry.php` contains inline comments and implements the same range validation as `api/submit.php` to keep behavior consistent.
- The page uses the existing `$pdo` connection from `config.php` and the CSRF helpers from `inc/csrf.php`.

API compatibility:
- The manual entry writes directly to the database using the same schema that `api/submit.php` uses. If you prefer the UI to POST to the API instead, replace the server-side insert with a fetch/cURL call to `/api/submit.php` and handle JSON response.

Sample curl (API) — send a measurement to the API directly:
curl -X POST https://your-host/api/submit.php \
  -H "Content-Type: application/json" \
  -d '{"station_serial":"SN-1001","temperature":21.5,"humidity":45.2,"pressure":1012.5,"light":400,"gas":12.1}'

Acceptance criteria satisfied:
- A PHP script (manual_entry.php) for data transfer / manual entry exists.
- Submitted entries include serial, timestamp, and values, and are saved in the DB.
- The script is accessible on the installed web server (requires login).
- Annotated source and this user documentation are provided.

Next steps (optional):
- Add client-side UX improvements (select station dropdown for owned stations).
- Add AJAX submission to avoid full-page reload and show inline success messages.

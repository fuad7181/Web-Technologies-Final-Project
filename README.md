# DPS (Merged Project) – Single Login + Role Dashboards

This merged project combines both uploaded projects into **one clean folder** and keeps a simple **MVC** structure:

- `models/` – DB connection + shared helpers
- `controllers/` – route handlers
- `views/` – pages/templates (customer, agent, admin, security)

## Setup (XAMPP/WAMP)
1. Copy the folder `merged_dps` into `htdocs` (or your server root).
2. Import `database.sql` into MySQL (phpMyAdmin): it creates a DB called `dps`.
3. In `models/config.php`, keep the DB credentials (default `root` / empty password) or edit them.
4. Open in browser:
 - `http://localhost/merged_dps/index.php`

## Single Login
Go to **Login** (`index.php`) and choose a role:

| Role | Identifier (Email or User ID) | Password |
|------|-------------------------------|----------|
| Customer | `customer@dps.com` or `cust1` | `customer1234` |
| Agent | `agent@dps.com` or `agent1` | `agent1234` |
| Admin | `admin@dps.com` or `admin` | `admin1234` |

## Notes
- Customer features (send money, cash out, pay bill, loan, profile) come from Project 1.
- Admin/Agent/Security screens (manage roles, verification, terms, password reset) come from Project 2.
- Passwords are stored as plain text (as requested).

If you see broken background images on some legacy dashboards, it is only UI (paths from the original project) and does not block functionality.

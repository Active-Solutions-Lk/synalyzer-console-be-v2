# 🚀 Full Production Deployment Guide

## 1. 📂 Core Requirements
- **Admin Console:** Linux VPS (e.g., AWS, DigitalOcean) with PHP 8.1+, MySQL, and a web server (Apache/Nginx).
- **Client Side (Collector/Analyzer):** Linux server or container (running the analyzer software) with PHP 8.1+, CLI access, and local MySQL.

## 2. 🌍 Admin Console Deployment (The Web API)
1. **Upload Code:** Copy everything inside `admin_console/` to your web server (e.g., `/var/www/html/synalyzer_admin`).
2. **Setup Database:**
   - Create a MySQL database `synalyzer_console`.
   - Import the `synalyzer_console.sql` schema (skip the test data).
   - Create a DB user with privileges.
3. **Configure:**
   - Edit `admin_console/config/config.php`.
   - Set DB credentials.
   - Set a **STRONG SECRET KEY** (keep this safe!).
   - Set `max_request_age` to 300 (5 mins).
4. **Permissions:**
   - `chmod -R 755 admin_console`.
   - `mkdir admin_console/logs`.
   - `chown -R www-data:www-data admin_console/logs`.
5. **Web Server:**
   - Set up HTTPS for `api.yourdomain.com`.
   - Point to `admin_console/` as root.
   - Ensure PHP modularity is enabled.

## 3. 🖥️ Client Deployment (The Collector/Analyzer)
1. **Upload Code:** Copy `client_side/` to the client machine (e.g., `/opt/synalyzer`).
2. **Setup Database:**
   - Ensure local `synalyzer` DB exists.
   - Import necessary tables (`companies`, `collectors`, etc.).
3. **Configure:**
   - Edit `client_side/config/config.php`.
   - Set DB credentials (local).
   - **IMPORTANT:** Set `secret_key` to match the **EXACT SAME KEY** from the Admin Console.
   - Set `admin_console_url` to `https://api.yourdomain.com/api/activate.php`.
   - Set `server` type ('analyzer' or 'collector') and a unique name.
4. **Permissions:**
   - `chmod +x client_side/api/activate.php`.
   - `mkdir client_side/logs`.

## 4. 🧹 Cleanup (Remove Test Files)
Delete the following files before deploying:
- `test_activation.php`
- `test_activation_web.html`
- `test_activation_backend.php`
- `TESTING_GUIDE.md`
- `synalyzer_console.sql` (keep a schema-only backup)

## 5. ✅ Verification
1. On the client machine, run:
   ```bash
   php client_side/api/activate.php --key="YOUR_ACTIVATION_KEY"
   ```
2. Check logs on both servers:
   - Admin: `admin_console/logs/api.log`
   - Client: `client_side/logs/activation.log`

---
**Date:** 2026-02-15
**Version:** 1.0.0

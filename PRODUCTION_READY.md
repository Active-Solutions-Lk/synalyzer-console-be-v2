# 🚀 Production Deployment Checklist - Admin Console

## 1. 📂 Core Configuration
**File:** `admin_console/config/config.php`

### 🔒 Database Credentials
Update with your production database details (e.g., AWS RDS, DigitalOcean Managed DB, or local Linux DB).
```php
'db' => [
    'host' => 'localhost',         // Change if using remote DB
    'database' => 'synalyzer_console',
    'username' => 'prod_user',     // ⚠️ DO NOT use 'root'
    'password' => 'StrongPass!23', // ⚠️ Use a strong password
],
```

### 🔑 Security Keys
**CRITICAL:** This key MUST be unique and 32+ characters. It must match the key in `client_side`.
```php
'secret_key' => 'REPLACE_THIS_WITH_A_VERY_LONG_RANDOM_STRING_FOR_PROD',
```

### 🌐 API Settings
Review these settings for your production environment.
```php
'api' => [
    'timezone' => 'Asia/Singapore', // Set to your server's timezone
    'max_request_age' => 300,       // 5 minutes (adjust if system clocks drift)
    'enable_logging' => true,       // Keep true for debugging initial issues
],
```

## 2. 🗄️ Database Setup
1. Create the `synalyzer_console` database on your production MySQL server.
2. Import the schema (structure only, clear the test data).
   ```sql
   -- Run this to clear test data before export/import if needed
   TRUNCATE TABLE projects;
   TRUNCATE TABLE analyzers;
   TRUNCATE TABLE devices;
   TRUNCATE TABLE collectors;
   TRUNCATE TABLE end_customer;
   ```
3. Create a dedicated MySQL user with limited privileges (SELECT, INSERT, UPDATE on specific tables).

## 3. 📂 File Permissions (Linux)
Ensure the web server user (usually `www-data` or `apache`) can write to the logs.
```bash
cd /path/to/synalyzer/admin_console
mkdir -p logs
chmod 755 logs
chown -R www-data:www-data logs
```

## 4. 🔒 Web Server (Apache/Nginx/Litespeed)
1. **Enable HTTPS:** You MUST use SSL (Let's Encrypt is free) to secure the API key during transit.
2. **Point Domain:** Set your domain (e.g., `api.synalyzer.com`) to point to the `admin_console` folder (or root).
3. **Block Direct Access:** If possible, restrict access to `api/` endpoints to known IP ranges or use rate limiting in valid config.

## 5. 🧹 Cleanup
Remove these test-related files from the production server to avoid confusion:
* `test_activation.php`
* `test_activation_web.html`
* `test_activation_backend.php`
* `TESTING_GUIDE.md`

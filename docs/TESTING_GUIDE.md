# 🧪 Testing Guide - Windows/XAMPP Environment

## 📋 Quick Start

This guide will help you test the Synalyzer Activation API on your local Windows machine using XAMPP.

## ✅ Prerequisites

- ✅ XAMPP installed (Apache + MySQL running)
- ✅ PHP 7.4 or higher
- ✅ phpMyAdmin accessible
- ✅ All project files in `c:\xampp\htdocs\synalyzer\`

## 🚀 Step-by-Step Setup

### Step 1: Import Database

1. **Open phpMyAdmin**
   - URL: `http://localhost/phpmyadmin`
   - Login with your MySQL credentials (default: root / no password)

2. **Create Database**
   - Click "New" in the left sidebar
   - Database name: `synalyzer_console`
   - Collation: `utf8mb4_general_ci`
   - Click "Create"

3. **Import SQL File**
   - Select the `synalyzer_console` database
   - Click "Import" tab
   - Choose file: `c:\xampp\htdocs\synalyzer\synalyzer_console.sql`
   - Click "Go"
   - ✅ You should see: "Import has been successfully finished"

### Step 2: Configure Admin Console

1. **Copy Configuration File**
   ```
   Copy: admin_console/config/config.example.php
   To: admin_console/config/config.php
   ```

2. **Edit `admin_console/config/config.php`**
   ```php
   return [
       'db' => [
           'host' => 'localhost',
           'port' => 3306,
           'database' => 'synalyzer_console',
           'username' => 'root',
           'password' => '',  // Your XAMPP MySQL password (usually empty)
           'charset' => 'utf8mb4',
       ],
       
       'secret_key' => 'test_secret_key_32_characters_min',
       
       'api' => [
           'timezone' => 'Asia/Kolkata',
           'max_request_age' => 300,
           'enable_logging' => true,
           'log_file' => __DIR__ . '/../logs/api.log',
       ],
   ];
   ```

3. **Create Logs Directory**
   - Create folder: `admin_console/logs/`
   - Make sure it's writable

### Step 3: Test Using Web Interface

1. **Open Test Interface**
   - URL: `http://localhost/synalyzer/test_activation_web.html`

2. **Select a Test Activation Key**
   - Click on any of the blue activation key boxes
   - The key will auto-fill in the form

3. **Configure Test Parameters**
   - **Activation Key**: `ANL-TEST-1234-ABCD` (or click to select)
   - **Server Name**: `Test-Analyzer-01`
   - **Server Type**: `Analyzer`
   - **Domain**: `http://localhost` (optional)

4. **Click "Test Activation"**
   - Wait for the response
   - ✅ Success: Green box with activation details
   - ❌ Error: Red box with error message

### Step 4: Verify in Database

1. **Check Analyzers Table**
   ```sql
   SELECT * FROM analyzers;
   ```
   - You should see a new analyzer record with your IP

2. **Check Projects Table**
   ```sql
   SELECT * FROM projects WHERE activation_key = 'ANL-TEST-1234-ABCD';
   ```
   - The `analyzer_id` field should now be populated

3. **Check Logs**
   - File: `admin_console/logs/api.log`
   - Should contain activation request details

## 🧪 Test Scenarios

### Test 1: First-Time Activation ✅

```
Activation Key: ANL-TEST-1234-ABCD
Expected Result: SUCCESS
- New analyzer created
- Project linked to analyzer
- Returns company details, devices, etc.
```

### Test 2: Re-Activation (Same IP) ✅

```
Activation Key: ANL-TEST-1234-ABCD (same key again)
Expected Result: SUCCESS
- Existing analyzer updated
- Same IP detected, re-activation allowed
```

### Test 3: Different IP Activation ❌

```
Activation Key: ANL-TEST-1234-ABCD (already activated)
From Different Computer/IP
Expected Result: ERROR
- Error: "Already activated by another analyzer"
- Shows the IP that originally activated
```

### Test 4: Invalid Activation Key ❌

```
Activation Key: INVALID-KEY-9999
Expected Result: ERROR
- Error: "Invalid activation key"
```

## 📊 Available Test Data

### Test Activation Keys

| Activation Key | Type | Customer | Devices | Status |
|----------------|------|----------|---------|--------|
| `ANL-TEST-1234-ABCD` | Analyzer | Test Company Alpha | 3 | Not Activated |
| `ANL-TEST-5678-EFGH` | Analyzer | Test Company Beta | 2 | Not Activated |
| `COL-TEST-9012-IJKL` | Collector | Test Company Gamma | 1 | Not Activated |

### Sample Companies

1. **Test Company Alpha**
   - Contact: John Doe
   - Email: john@testcompany.com
   - Phone: 9876543210

2. **Test Company Beta**
   - Contact: Jane Smith
   - Email: jane@betacompany.com
   - Phone: 8765432109

3. **Test Company Gamma**
   - Contact: Bob Wilson
   - Email: bob@gammacompany.com
   - Phone: 7654321098

### Sample Device Keys

- `DEV-KEY-ALPHA-001` (30 days)
- `DEV-KEY-ALPHA-002` (30 days)
- `DEV-KEY-ALPHA-003` (60 days)
- `DEV-KEY-BETA-001` (30 days)
- `DEV-KEY-BETA-002` (30 days)
- `DEV-KEY-GAMMA-001` (90 days)

## 🔧 Troubleshooting

### Error: "Connection refused"

**Cause**: Apache is not running or wrong URL

**Solution**:
1. Start XAMPP Apache
2. Verify URL: `http://localhost/synalyzer/admin_console/api/activate.php`

### Error: "Database connection failed"

**Cause**: Wrong database credentials

**Solution**:
1. Check `admin_console/config/config.php`
2. Verify database name: `synalyzer_console`
3. Check MySQL is running in XAMPP

### Error: "Decryption failed"

**Cause**: Secret key mismatch

**Solution**:
1. Ensure `secret_key` is: `test_secret_key_32_characters_min`
2. Same key in both admin and client configs

### Error: "Class 'Encryption' not found"

**Cause**: Missing library files

**Solution**:
1. Verify `admin_console/lib/Encryption.php` exists
2. Check file paths in require statements

### No Response / Blank Page

**Cause**: PHP errors

**Solution**:
1. Check PHP error log: `c:\xampp\php\logs\php_error_log`
2. Enable error display temporarily:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

## 🔄 Reset Test Data

To reset and test again:

```sql
-- Clear all activations
UPDATE projects SET analyzer_id = NULL;
DELETE FROM analyzers;

-- Or re-import the SQL file
```

## 📝 Testing Checklist

- [ ] Database imported successfully
- [ ] Config file created and edited
- [ ] Apache and MySQL running
- [ ] Web interface accessible
- [ ] First activation successful
- [ ] Re-activation from same IP works
- [ ] Different IP activation blocked
- [ ] Invalid key rejected
- [ ] Data visible in database
- [ ] Logs being created

## 🎯 Expected Success Response

```json
{
  "success": true,
  "message": "Activation successful",
  "data": {
    "server_id": 1,
    "server_type": "analyzer",
    "project_id": 1,
    "activation_key": "ANL-TEST-1234-ABCD",
    "company": {
      "id": 1,
      "name": "Test Company Alpha",
      "contact_person": "John Doe",
      "email": "john@testcompany.com"
    },
    "collector": {
      "id": 1,
      "name": "Collector-Mumbai-01",
      "secret_key": "collector_secret_key_mumbai_001"
    },
    "port": {
      "id": 1,
      "port": 514
    },
    "devices": [
      {
        "device_key": "DEV-KEY-ALPHA-001",
        "log_duration": 30
      }
    ],
    "device_count": 10
  }
}
```

## 🚀 Next Steps

After successful testing on Windows:

1. ✅ Deploy to Linux VPS (production)
2. ✅ Update configuration with production credentials
3. ✅ Enable HTTPS/SSL
4. ✅ Set strong secret keys
5. ✅ Configure firewall rules
6. ✅ Set up monitoring and alerts

## 📞 Support

If you encounter issues:

1. Check the troubleshooting section above
2. Review `admin_console/logs/api.log`
3. Check PHP error logs
4. Verify database structure matches SQL file

---

**Environment**: Windows/XAMPP (Testing)  
**Production**: Linux VPS  
**Version**: 1.2.0  
**Last Updated**: 2026-02-15

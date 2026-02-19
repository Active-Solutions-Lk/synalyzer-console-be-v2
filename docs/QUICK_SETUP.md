# Quick Setup Guide - Synalyzer Activation API

## ✅ Test Results

All security features are working correctly:
- ✓ AES-256-GCM Encryption/Decryption
- ✓ Timestamp Validation (Replay Attack Prevention)
- ✓ Unique Nonce Generation
- ✓ Activation Key Generation
- ✓ Password Hashing (bcrypt)
- ✓ Full Request/Response Cycle
- ✓ Tamper Detection

## 📁 Project Structure

```
synalyzer/
├── admin_console/              # Deploy to VPS (Singapore/Mumbai)
│   ├── api/
│   │   └── activate.php        # Main API endpoint
│   ├── lib/
│   │   ├── Encryption.php      # Encryption library
│   │   ├── ApiResponse.php     # API response handler
│   │   └── Database.php        # Database wrapper
│   ├── config/
│   │   ├── config.php          # Configuration (create from example)
│   │   └── config.example.php  # Example configuration
│   └── logs/                   # API logs
│
├── client_side/                # Deploy to Analyzer/Collector servers
│   ├── api/
│   │   └── activate.php        # Activation script
│   ├── lib/
│   │   ├── Encryption.php      # Encryption library
│   │   └── Database.php        # Database wrapper
│   ├── config/
│   │   ├── config.php          # Configuration (create from example)
│   │   └── config.example.php  # Example configuration
│   └── logs/                   # Activation logs
│
├── test_activation.php         # Test script
├── README_ACTIVATION_API.md    # Full documentation
└── .gitignore                  # Git ignore file
```

## 🚀 Quick Start

### Step 1: Admin Console Setup (VPS Server)

1. **Copy configuration file:**
   ```bash
   cd admin_console/config
   cp config.example.php config.php
   ```

2. **Edit `config.php`:**
   ```php
   'db' => [
       'host' => 'localhost',
       'database' => 'synalyzer_console',
       'username' => 'your_db_user',
       'password' => 'your_db_password',
   ],
   
   // Generate with: openssl rand -base64 32
   'secret_key' => 'YOUR_STRONG_SECRET_KEY_HERE',
   ```

3. **Set permissions:**
   ```bash
   chmod 755 admin_console/logs
   chmod 644 admin_console/config/config.php
   ```

4. **Configure web server** to serve `admin_console/api/activate.php`
   - Example: `https://admin-console.synalyzer.com/api/activate.php`

### Step 2: Client Side Setup (Analyzer/Collector)

1. **Copy configuration file:**
   ```bash
   cd client_side/config
   cp config.example.php config.php
   ```

2. **Edit `config.php`:**
   ```php
   'db' => [
       'host' => 'localhost',
       'database' => 'synalyzer',
       'username' => 'your_db_user',
       'password' => 'your_db_password',
   ],
   
   'admin_console_url' => 'https://admin-console.synalyzer.com/api/activate.php',
   
   // MUST MATCH admin console secret_key!
   'secret_key' => 'YOUR_STRONG_SECRET_KEY_HERE',
   
   'server' => [
       'type' => 'analyzer', // or 'collector'
       'name' => 'analyzer-01',
   ],
   ```

3. **Set permissions:**
   ```bash
   chmod 755 client_side/logs
   chmod 644 client_side/config/config.php
   ```

### Step 3: Generate Activation Key

In your admin console database (`synalyzer_console`), create a project with an activation key:

```sql
-- Example: Insert a test project
INSERT INTO projects (activation_key, project_type_id, port_id, admin_id, collector_id, device_count, created_at, updated_at)
VALUES ('TEST-1234-5678-9ABC', 1, 1, 1, 1, 10, NOW(), NOW());
```

Or use the PHP helper:
```php
require_once 'admin_console/lib/Encryption.php';
echo Encryption::generateActivationKey('ANL'); // For Analyzer
echo Encryption::generateActivationKey('COL'); // For Collector
```

### Step 4: Activate Client

On the analyzer/collector server:

```bash
cd client_side/api
php activate.php --activation-key=TEST-1234-5678-9ABC --server-name="Analyzer-01"
```

Expected output:
```
=== Synalyzer Activation Script ===

Client IP: 192.168.1.100
Server Type: analyzer
Server Name: Analyzer-01
Activation Key: TEST-1234-5678-9ABC

Encrypting request...
Sending request to admin console...
Response received (HTTP 200)

✓ Activation successful!

=== Activation Complete ===
✓ Server is now activated and ready to use!
```

## 🔒 Security Checklist

- [ ] Strong secret key generated (32+ characters)
- [ ] Secret keys match between admin console and clients
- [ ] Database credentials secured
- [ ] HTTPS enabled on admin console
- [ ] SSL certificate verification enabled in production
- [ ] Firewall rules configured
- [ ] Log files monitored
- [ ] Config files excluded from version control (.gitignore)

## 🧪 Testing

Run the test script to verify encryption is working:

```bash
php test_activation.php
```

All tests should pass with ✓ marks.

## 📝 Important Notes

1. **Secret Key Synchronization**: The `secret_key` in `admin_console/config/config.php` MUST match `client_side/config/config.php` on all servers.

2. **Database Separation**: 
   - Admin Console uses: `synalyzer_console` database
   - Client Side uses: `synalyzer` database

3. **Cross-VPS Communication**: The system is designed for servers in different locations (Mumbai/Singapore) to communicate securely over HTTPS.

4. **Activation Key Format**: 
   - Analyzer: `ANL-XXXX-XXXX-XXXX`
   - Collector: `COL-XXXX-XXXX-XXXX`
   - Generic: `XXXX-XXXX-XXXX`

## 🐛 Troubleshooting

### "Decryption failed" error
- **Cause**: Secret keys don't match
- **Fix**: Ensure both config files have identical `secret_key`

### "Invalid activation key" error
- **Cause**: Key not found in database
- **Fix**: Verify the key exists in `projects` table

### "Connection timeout" error
- **Cause**: Cannot reach admin console
- **Fix**: Check URL, firewall, and network connectivity

## 📚 Full Documentation

See `README_ACTIVATION_API.md` for complete documentation including:
- Detailed API specifications
- Error codes reference
- Security best practices
- Advanced configuration options

## 🎯 Next Steps

1. Deploy to production VPS servers
2. Test with real activation keys
3. Monitor logs for any issues
4. Set up automated backups
5. Configure monitoring/alerting

---

**Status**: ✅ Ready for Production  
**Version**: 1.0.0  
**Last Updated**: 2026-02-15

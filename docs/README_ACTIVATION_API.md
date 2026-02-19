# Synalyzer Activation API System

## Overview

This is a secure activation API system for the Synalyzer platform, enabling analyzer and collector servers to activate themselves using activation keys generated from the admin console.

## Architecture

```
┌─────────────────────┐         Encrypted         ┌──────────────────────┐
│  Client (Analyzer/  │ ──────── HTTPS ─────────> │   Admin Console      │
│     Collector)      │ <─────── Request ───────  │   (Singapore/Mumbai) │
└─────────────────────┘                           └──────────────────────┘
        │                                                    │
        │ Stores config                                     │ Validates & 
        ▼                                                    ▼ Returns config
┌─────────────────────┐                           ┌──────────────────────┐
│  synalyzer DB       │                           │ synalyzer_console DB │
│  (Local)            │                           │  (Central)           │
└─────────────────────┘                           └──────────────────────┘
```

## Security Features

- **AES-256-GCM Encryption**: All requests and responses are encrypted
- **HMAC-SHA256 Authentication**: Message authentication to prevent tampering
- **Timestamp Validation**: Prevents replay attacks (5-minute window)
- **Nonce Generation**: Unique request identifiers
- **Secret Key Validation**: Shared secret between client and server
- **SSL/TLS Support**: HTTPS communication (configure in production)

## Directory Structure

```
synalyzer/
├── admin_console/          # Admin Console (VPS Server)
│   ├── api/
│   │   └── activate.php    # Main activation endpoint
│   ├── lib/
│   │   ├── Encryption.php  # Encryption library
│   │   ├── ApiResponse.php # API response handler
│   │   └── Database.php    # Database wrapper
│   └── config/
│       └── config.php      # Configuration file
│
└── client_side/            # Client (Analyzer/Collector)
    ├── api/
    │   └── activate.php    # Activation script
    ├── lib/
    │   ├── Encryption.php  # Encryption library
    │   └── Database.php    # Database wrapper
    └── config/
        └── config.php      # Configuration file
```

## Installation

### Admin Console (VPS Server)

1. **Upload files** to your VPS server (Singapore/Mumbai)
   ```bash
   # Upload admin_console directory to your web server
   # Example: /var/www/html/synalyzer/admin_console
   ```

2. **Configure database** in `admin_console/config/config.php`:
   ```php
   'db' => [
       'host' => 'localhost',
       'database' => 'synalyzer_console',
       'username' => 'your_username',
       'password' => 'your_password',
   ],
   ```

3. **Set secret key** (IMPORTANT - must be same on all servers):
   ```php
   'secret_key' => 'YOUR_STRONG_32_CHAR_SECRET_KEY_HERE',
   ```

4. **Create logs directory**:
   ```bash
   mkdir -p admin_console/logs
   chmod 755 admin_console/logs
   ```

5. **Configure web server** to point to `admin_console/api/activate.php`
   - Example URL: `https://admin-console.synalyzer.com/api/activate.php`

### Client Side (Analyzer/Collector)

1. **Upload files** to your analyzer/collector server
   ```bash
   # Upload client_side directory
   ```

2. **Configure database** in `client_side/config/config.php`:
   ```php
   'db' => [
       'host' => 'localhost',
       'database' => 'synalyzer',
       'username' => 'your_username',
       'password' => 'your_password',
   ],
   ```

3. **Set admin console URL**:
   ```php
   'admin_console_url' => 'https://admin-console.synalyzer.com/api/activate.php',
   ```

4. **Set secret key** (MUST MATCH admin console):
   ```php
   'secret_key' => 'YOUR_STRONG_32_CHAR_SECRET_KEY_HERE',
   ```

5. **Set server type**:
   ```php
   'server' => [
       'type' => 'analyzer', // or 'collector'
       'name' => 'analyzer-01',
   ],
   ```

## Usage

### Step 1: Generate Activation Key (Admin Console)

In the admin console, create a project and generate an activation key. The key will be in format:
```
XXX-XXXX-XXXX-XXXX
```

### Step 2: Activate Client Server

On the analyzer/collector server, run:

```bash
cd client_side/api
php activate.php --activation-key=XXX-XXXX-XXXX --server-name="Analyzer-01"
```

Optional parameters:
```bash
php activate.php \
  --activation-key=XXX-XXXX-XXXX \
  --server-name="My Analyzer Server" \
  --domain=https://analyzer.example.com
```

### Step 3: Verify Activation

The script will:
1. ✓ Encrypt the activation request
2. ✓ Send to admin console
3. ✓ Receive encrypted configuration
4. ✓ Store configuration in local database
5. ✓ Display activation details

Example output:
```
=== Synalyzer Activation Script ===

Client IP: 192.168.1.100
Server Type: analyzer
Server Name: Analyzer-01
Activation Key: XXX-XXXX-XXXX

Encrypting request...
Sending request to admin console...
URL: https://admin-console.synalyzer.com/api/activate.php

Response received (HTTP 200)

Decrypting response...
✓ Activation successful!

Storing activation data in local database...
✓ Company record created
✓ Collector record created

=== Activation Complete ===

Configuration Details:
---------------------
Server ID: 123
Server Type: analyzer
Project ID: 456
Collector ID: 789
Collector Name: Default Collector
Port: 514

✓ Server is now activated and ready to use!
```

## API Request/Response Format

### Request (Encrypted)

```json
{
  "encrypted": true,
  "payload": "base64_encrypted_data"
}
```

Decrypted payload contains:
```json
{
  "server_type": "analyzer",
  "activation_key": "XXX-XXXX-XXXX",
  "client_ip": "192.168.1.100",
  "secret_key": "shared_secret",
  "timestamp": 1234567890,
  "nonce": "random_string",
  "server_name": "Analyzer-01",
  "domain": "https://analyzer.example.com"
}
```

### Response (Encrypted)

```json
{
  "encrypted": true,
  "payload": "base64_encrypted_data"
}
```

Decrypted payload contains:
```json
{
  "status": "success",
  "message": "Activation successful",
  "timestamp": 1234567890,
  "data": {
    "server_id": 123,
    "server_type": "analyzer",
    "project_id": 456,
    "project_type": "Standard",
    "activation_key": "ANL-1234-5678-9ABC",
    "collector": {
      "id": 789,
      "name": "Collector-01",
      "ip": "192.168.1.200",
      "domain": "https://collector.example.com",
      "secret_key": "collector_secret"
    },
    "port": {
      "id": 1,
      "port": 514
    },
    "company": {
      "id": 10,
      "name": "Alpha Corp",
      "address": "123 Main St, New York",
      "contact_person": "John Doe",
      "tel": "1234567890",
      "email": "contact@alphacorp.com"
    },
    "devices": [
      {
        "id": 1,
        "device_key": "DEV-KEY-001",
        "log_duration": 30,
        "package_start_at": "2026-01-01",
        "package_end_at": "2026-12-31"
      },
      {
        "id": 2,
        "device_key": "DEV-KEY-002",
        "log_duration": 30,
        "package_start_at": "2026-01-01",
        "package_end_at": "2026-12-31"
      }
    ],
    "device_count": 10
  }
}
```

## Error Codes

| Code | Description |
|------|-------------|
| `EMPTY_REQUEST` | Request body is empty |
| `INVALID_JSON` | Invalid JSON format |
| `INVALID_FORMAT` | Request format is invalid |
| `DECRYPTION_FAILED` | Failed to decrypt request (wrong secret key) |
| `MISSING_FIELDS` | Required fields are missing |
| `TIMESTAMP_EXPIRED` | Request timestamp is too old (replay attack) |
| `INVALID_SERVER_TYPE` | Server type must be 'analyzer' or 'collector' |
| `INVALID_ACTIVATION_KEY` | Activation key not found in database |
| `ALREADY_ACTIVATED` | Activation key already used by another server (different IP) |
| `SERVER_ERROR` | Internal server error |

## Security Best Practices

1. **Secret Key Management**:
   - Use a strong, random 32+ character secret key
   - Store in environment variables, not in code
   - Rotate keys periodically
   - Never commit keys to version control

2. **SSL/TLS**:
   - Always use HTTPS in production
   - Enable SSL certificate verification
   - Use valid SSL certificates

3. **Database Security**:
   - Use strong database passwords
   - Limit database user permissions
   - Enable database encryption at rest

4. **Server Security**:
   - Keep PHP and libraries updated
   - Enable firewall rules
   - Monitor API logs for suspicious activity
   - Implement rate limiting

5. **Network Security**:
   - Use VPN for server-to-server communication
   - Whitelist IP addresses if possible
   - Monitor network traffic

## Troubleshooting

### "Decryption failed" error

**Cause**: Secret keys don't match between client and server

**Solution**: Ensure `secret_key` in both config files is identical

### "Invalid activation key" error

**Cause**: Activation key not found in database

**Solution**: Verify the activation key exists in the `projects` table

### "Connection timeout" error

**Cause**: Cannot reach admin console URL

**Solution**: 
- Check `admin_console_url` in client config
- Verify firewall rules
- Test URL manually with curl

### "Database connection failed" error

**Cause**: Database credentials are incorrect

**Solution**: Verify database credentials in config file

## Maintenance

### Logs

- Admin Console: `admin_console/logs/api.log`
- Client Side: `client_side/logs/activation.log`
- PHP Error Log: Check your PHP error log location

### Database Cleanup

Periodically clean up old activation logs and inactive servers.

## Support

For issues or questions, contact your system administrator.

---

**Version**: 1.0.0  
**Last Updated**: 2026-02-15

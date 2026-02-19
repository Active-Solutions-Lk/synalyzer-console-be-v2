# Activation Key Validation & Security

## 🔐 How Activation Validation Works

The admin console now implements **multi-level validation** to prevent unauthorized use of activation keys.

## 📋 Validation Flow

### Step 1: Activation Key Exists
```php
// Check if activation key is valid
$project = $db->queryOne(
    "SELECT * FROM projects WHERE activation_key = ?",
    [$activation_key]
);

if (!$project) {
    return ERROR: 'Invalid activation key'
}
```

### Step 2: Check If Already Activated

#### For Analyzers:
```php
// Check if project already has an analyzer
if ($project['analyzer_id'] !== null) {
    
    // Get existing analyzer details
    $existingAnalyzer = SELECT * FROM analyzers WHERE id = $project['analyzer_id'];
    
    // Compare IP addresses
    if ($existingAnalyzer['ip'] !== $requestData['client_ip']) {
        return ERROR: 'Already activated by another analyzer'
    }
    
    // Same IP = Allow re-activation (server reinstall scenario)
}
```

#### For Collectors:
```php
// Check if collector is already active
$existingCollector = SELECT * FROM collectors 
                     WHERE id = $project['collector_id'] 
                     AND is_active = 1;

if ($existingCollector && $existingCollector['ip'] !== $requestData['client_ip']) {
    return ERROR: 'Already activated by another collector'
}
```

## 🎯 Activation Scenarios

### ✅ Scenario 1: First-Time Activation
```
Request: Activation key "ANL-1234-5678-9ABC" from IP 192.168.1.100
Status: projects.analyzer_id = NULL
Result: ✅ ALLOWED - First activation, create new analyzer
```

### ✅ Scenario 2: Re-Activation from Same IP (Server Reinstall)
```
Request: Activation key "ANL-1234-5678-9ABC" from IP 192.168.1.100
Status: projects.analyzer_id = 5, analyzers[5].ip = 192.168.1.100
Result: ✅ ALLOWED - Same IP, update existing analyzer
```

### ❌ Scenario 3: Activation from Different IP (Unauthorized)
```
Request: Activation key "ANL-1234-5678-9ABC" from IP 192.168.1.200
Status: projects.analyzer_id = 5, analyzers[5].ip = 192.168.1.100
Result: ❌ BLOCKED - Different IP, already activated
Error: "Activation key already used by another analyzer (IP: 192.168.1.100)"
```

### ✅ Scenario 4: Reactivation After Deactivation
```
Request: Activation key "ANL-1234-5678-9ABC" from IP 192.168.1.100
Status: projects.analyzer_id = 5, analyzers[5].status = 0 (inactive)
Result: ✅ ALLOWED - Reactivating previously deactivated server
```

## 🔍 Validation Logic Summary

| Condition | Analyzer | Collector | Result |
|-----------|----------|-----------|--------|
| First activation | `analyzer_id = NULL` | `collector not active` | ✅ Allow |
| Same IP re-activation | `analyzer.ip = request.ip` | `collector.ip = request.ip` | ✅ Allow |
| Different IP | `analyzer.ip ≠ request.ip` | `collector.ip ≠ request.ip` | ❌ Block |
| Invalid key | Key not in projects | Key not in projects | ❌ Block |

## 📊 Database State Tracking

### Projects Table
```sql
CREATE TABLE projects (
    id INT PRIMARY KEY,
    activation_key TEXT,
    analyzer_id INT,      -- NULL = not activated, ID = activated
    collector_id INT,     -- References collector
    ...
);
```

### Analyzers Table
```sql
CREATE TABLE analyzers (
    id INT PRIMARY KEY,
    ip VARCHAR(45),       -- Used for IP validation
    status TINYINT,       -- 1 = active, 0 = inactive
    ...
);
```

### Collectors Table
```sql
CREATE TABLE collectors (
    id INT PRIMARY KEY,
    ip VARCHAR(100),      -- Used for IP validation
    is_active TINYINT,    -- 1 = active, 0 = inactive
    ...
);
```

## 🚨 Error Responses

### Error: Already Activated (Different IP)
```json
{
  "encrypted": true,
  "payload": "encrypted_data"
}

// Decrypted:
{
  "status": "error",
  "message": "Activation key already used by another analyzer (IP: 192.168.1.100)",
  "error_code": "ALREADY_ACTIVATED",
  "timestamp": 1771096850
}
```

### Error: Invalid Activation Key
```json
{
  "status": "error",
  "message": "Invalid activation key",
  "error_code": "INVALID_ACTIVATION_KEY",
  "timestamp": 1771096850
}
```

## 🔄 Re-Activation Policy

### When Re-Activation is Allowed:
1. ✅ **Same IP Address** - Server reinstall, OS upgrade, etc.
2. ✅ **Manual Deactivation** - Admin manually deactivated the server
3. ✅ **Status Update** - Reactivating an inactive server

### When Re-Activation is Blocked:
1. ❌ **Different IP Address** - Prevents key sharing/theft
2. ❌ **Active Server Exists** - Prevents duplicate activations
3. ❌ **Invalid Key** - Key doesn't exist in database

## 🛡️ Security Benefits

### 1. Prevents Key Sharing
```
Company A buys activation key → Activates on Server A (IP: 192.168.1.100)
Company B tries to use same key → Activates on Server B (IP: 192.168.2.200)
Result: ❌ BLOCKED - Different IP detected
```

### 2. Allows Legitimate Re-Activation
```
Server A (IP: 192.168.1.100) crashes → Admin reinstalls OS
Admin re-activates with same key → Same IP detected
Result: ✅ ALLOWED - Legitimate server recovery
```

### 3. Tracks Activation History
```sql
-- View activation history
SELECT 
    p.activation_key,
    a.ip as analyzer_ip,
    a.created_at as first_activation,
    a.updated_at as last_activation,
    a.status
FROM projects p
LEFT JOIN analyzers a ON p.analyzer_id = a.id
WHERE p.activation_key = 'ANL-1234-5678-9ABC';
```

## 🔧 Manual Override (Admin Use)

If you need to manually reset an activation key:

### Option 1: Clear Analyzer Assignment
```sql
-- Allow key to be used by a new analyzer
UPDATE projects 
SET analyzer_id = NULL 
WHERE activation_key = 'ANL-1234-5678-9ABC';
```

### Option 2: Deactivate Current Server
```sql
-- Deactivate current analyzer
UPDATE analyzers 
SET status = 0 
WHERE id = (
    SELECT analyzer_id 
    FROM projects 
    WHERE activation_key = 'ANL-1234-5678-9ABC'
);
```

### Option 3: Update IP Address
```sql
-- Update analyzer IP (if server IP changed)
UPDATE analyzers 
SET ip = '192.168.1.200' 
WHERE id = (
    SELECT analyzer_id 
    FROM projects 
    WHERE activation_key = 'ANL-1234-5678-9ABC'
);
```

## 📝 Client-Side Error Handling

The client activation script will receive the error:

```bash
$ php activate.php --activation-key=ANL-1234-5678-9ABC

=== Synalyzer Activation Script ===

Client IP: 192.168.1.200
Server Type: analyzer
Activation Key: ANL-1234-5678-9ABC

Encrypting request...
Sending request to admin console...
Response received (HTTP 400)

Decrypting response...
ERROR: Activation key already used by another analyzer (IP: 192.168.1.100)
```

## 🎯 Best Practices

### For Administrators:
1. ✅ Generate unique activation keys per customer
2. ✅ Track which keys are assigned to which customers
3. ✅ Monitor activation attempts in logs
4. ✅ Deactivate keys when customers cancel service

### For Customers:
1. ✅ Keep activation keys secure
2. ✅ Use same IP for re-activation when possible
3. ✅ Contact support if IP needs to change
4. ✅ Don't share activation keys

## 📊 Monitoring & Logging

All activation attempts are logged:

```php
ApiResponse::logRequest('activate', $requestData);
// Logs to: admin_console/logs/api.log

// Example log entry:
[2026-02-15 01:30:00] activate - IP: 192.168.1.200 - Data: {
    "server_type": "analyzer",
    "activation_key": "***MASKED***",
    "client_ip": "192.168.1.200"
}
```

## 🔐 Summary

The activation system now provides:
- ✅ **One activation per key** (prevents unauthorized sharing)
- ✅ **IP-based validation** (tracks which server is activated)
- ✅ **Re-activation support** (allows server reinstalls from same IP)
- ✅ **Clear error messages** (helps troubleshoot issues)
- ✅ **Audit trail** (logs all activation attempts)

---

**Security Level**: ✅ **Production-Ready**  
**Last Updated**: 2026-02-15  
**Version**: 1.2.0

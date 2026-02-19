# Activation API Enhancement - Complete Data Transfer

## 🎯 Problem Solved

The initial implementation was missing critical data that the client needs to populate its local database tables:
- **Company details** (from `end_customer` table)
- **Device keys** (from `devices` table)
- **Port information** (complete port object, not just ID)

## ✅ What Was Updated

### 1. Admin Console API (`admin_console/api/activate.php`)

**Enhanced Response Data:**

Now returns complete information for both analyzer and collector activations:

```php
$responseData = [
    'server_id' => $analyzerId,
    'server_type' => 'analyzer',
    'project_id' => $project['id'],
    'project_type' => $project['project_type_name'],
    'activation_key' => $requestData['activation_key'],
    
    // Complete collector information
    'collector' => [
        'id' => $collector['id'],
        'name' => $collector['name'],
        'ip' => $collector['ip'],
        'domain' => $collector['domain'],
        'secret_key' => $collector['secret_key'],
    ],
    
    // Complete port information
    'port' => [
        'id' => $port['id'],
        'port' => $port['port'],
    ],
    
    // Company/customer details (NEW!)
    'company' => [
        'id' => $company['id'],
        'name' => $company['company'],
        'address' => $company['address'],
        'contact_person' => $company['contact_person'],
        'tel' => $company['tel'],
        'email' => $company['email'],
    ],
    
    // Device keys array (NEW!)
    'devices' => [
        [
            'id' => $device['id'],
            'device_key' => $device['device_key'],
            'log_duration' => $device['log_duration'],
            'package_start_at' => $device['package_start_at'],
            'package_end_at' => $device['package_end_at'],
        ],
        // ... more devices
    ],
    
    'device_count' => $project['device_count'],
];
```

**Database Queries Added:**
- Fetches port details from `ports` table
- Fetches company/customer from `end_customer` table
- Fetches all device keys from `devices` table for the project

### 2. Client Side Activation Script (`client_side/api/activate.php`)

**Enhanced Database Storage:**

Now properly populates all local database tables:

#### Companies Table
```php
INSERT INTO companies (collector_id, port, act_key, name)
VALUES (
    $collector_id,
    $port_number,           // Now uses actual port number
    $activation_key,        // Now uses activation key as act_key
    $company_name          // Uses company name from response
)
```

#### Device Keys Table (NEW!)
```php
foreach ($devices as $device) {
    INSERT INTO device_keys (
        company_id,
        device_key,
        package_name,
        log_duration,
        package_start_at,
        package_end_at
    ) VALUES (...)
}
```

**Output Enhanced:**
```
=== Activation Complete ===

Configuration Details:
---------------------
Server ID: 123
Server Type: analyzer
Project ID: 456
Company ID: 10
Company Name: Alpha Corp
Contact Person: John Doe
Email: contact@alphacorp.com
Collector ID: 789
Collector Name: Collector-01
Port: 514
Device Count: 10
Device Keys Registered: 2

✓ Server is now activated and ready to use!
```

## 📊 Database Mapping

### Admin Console → Client Database

| Admin Console Table | Client Database Table | Data Transferred |
|---------------------|----------------------|------------------|
| `end_customer` | `companies` | Company name, contact info |
| `ports` | `companies.port` | Port number |
| `projects` | `companies.act_key` | Activation key |
| `collectors` | `collectors` | Collector details, secret key |
| `devices` | `device_keys` | Device keys, log duration, package dates |

## 🔄 Complete Data Flow

```
┌─────────────────────────────────────────────────┐
│  Admin Console (synalyzer_console DB)          │
│                                                 │
│  1. Validates activation_key in projects       │
│  2. Fetches end_customer details               │
│  3. Fetches ports information                  │
│  4. Fetches all devices for project            │
│  5. Fetches collector information              │
│                                                 │
│  Encrypts and sends ALL data ──────────┐       │
└─────────────────────────────────────────┼───────┘
                                          │
                                          ▼
┌─────────────────────────────────────────────────┐
│  Client (synalyzer DB)                          │
│                                                 │
│  1. Decrypts response                           │
│  2. Stores in companies table:                  │
│     - collector_id                              │
│     - port (actual port number)                 │
│     - act_key (activation key)                  │
│     - name (company name)                       │
│                                                 │
│  3. Stores in collectors table:                 │
│     - id, name, secret_key, ip, domain          │
│                                                 │
│  4. Stores in device_keys table:                │
│     - company_id                                │
│     - device_key                                │
│     - package_name                              │
│     - log_duration                              │
│     - package_start_at                          │
│     - package_end_at                            │
│                                                 │
└─────────────────────────────────────────────────┘
```

## 🧪 Testing

The enhanced API now transfers complete data. To test:

1. **Create test data in admin console:**
```sql
-- Insert end customer
INSERT INTO end_customer (company, address, contact_person, tel, email, status)
VALUES ('Test Corp', '123 Test St', 'John Doe', 1234567890, 'test@test.com', 1);

-- Insert port
INSERT INTO ports (port) VALUES (514);

-- Insert project with activation key
INSERT INTO projects (activation_key, project_type_id, port_id, admin_id, 
                     collector_id, end_customer_id, device_count)
VALUES ('TEST-1234-5678-9ABC', 1, 1, 1, 1, 1, 5);

-- Insert device keys for the project
INSERT INTO devices (project_id, device_key, log_duration, package_start_at, package_end_at)
VALUES 
(1, 'DEV-KEY-001', 30, '2026-01-01', '2026-12-31'),
(1, 'DEV-KEY-002', 30, '2026-01-01', '2026-12-31');
```

2. **Run activation on client:**
```bash
php client_side/api/activate.php \
  --activation-key=TEST-1234-5678-9ABC \
  --server-name="Test-Analyzer"
```

3. **Verify data in client database:**
```sql
-- Check companies table
SELECT * FROM companies WHERE act_key = 'TEST-1234-5678-9ABC';

-- Check device_keys table
SELECT * FROM device_keys WHERE company_id = (
    SELECT id FROM companies WHERE act_key = 'TEST-1234-5678-9ABC'
);

-- Check collectors table
SELECT * FROM collectors;
```

## 📝 Summary of Changes

### Files Modified:
1. ✅ `admin_console/api/activate.php` - Enhanced response with company and device data
2. ✅ `client_side/api/activate.php` - Enhanced storage to populate all tables
3. ✅ `README_ACTIVATION_API.md` - Updated documentation with new response format

### New Features:
- ✅ Company details transferred from `end_customer` table
- ✅ Complete port information (not just ID)
- ✅ All device keys transferred and stored
- ✅ Activation key stored in `companies.act_key`
- ✅ Enhanced output showing all transferred data

### Database Tables Now Properly Populated:
- ✅ `companies` - With correct port number and activation key
- ✅ `collectors` - With complete collector information
- ✅ `device_keys` - With all device keys from the project

## 🎉 Result

The client now receives **complete activation data** and can properly populate:
- ✅ `companies` table (with company name, port, activation key)
- ✅ `device_keys` table (with all device keys and package information)
- ✅ `collectors` table (with collector details)

All data needed for the analyzer/collector to function is now transferred during activation!

---

**Updated**: 2026-02-15  
**Version**: 1.1.0

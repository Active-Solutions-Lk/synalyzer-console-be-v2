# Complete Activation Data Flow

## 📊 Database Schema Mapping

### Admin Console (synalyzer_console) → Client (synalyzer)

```
┌──────────────────────────────────────────────────────────────────┐
│                    ADMIN CONSOLE DATABASE                        │
│                    (synalyzer_console)                           │
└──────────────────────────────────────────────────────────────────┘

┌─────────────────┐     ┌──────────────────┐     ┌──────────────┐
│   projects      │     │  end_customer    │     │    ports     │
├─────────────────┤     ├──────────────────┤     ├──────────────┤
│ id              │     │ id               │     │ id           │
│ activation_key  │────▶│ company          │────▶│ port         │
│ end_customer_id │     │ address          │     └──────────────┘
│ port_id         │     │ contact_person   │
│ collector_id    │     │ tel              │     ┌──────────────┐
│ device_count    │     │ email            │     │  collectors  │
└─────────────────┘     └──────────────────┘     ├──────────────┤
         │                                        │ id           │
         │              ┌──────────────────┐     │ name         │
         └─────────────▶│    devices       │     │ ip           │
                        ├──────────────────┤     │ domain       │
                        │ id               │     │ secret_key   │
                        │ project_id       │     └──────────────┘
                        │ device_key       │
                        │ log_duration     │
                        │ package_start_at │
                        │ package_end_at   │
                        └──────────────────┘

                              ▼ ▼ ▼
                        ENCRYPTED API CALL
                              ▼ ▼ ▼

┌──────────────────────────────────────────────────────────────────┐
│                      CLIENT DATABASE                             │
│                       (synalyzer)                                │
└──────────────────────────────────────────────────────────────────┘

┌─────────────────┐     ┌──────────────────┐     ┌──────────────┐
│   companies     │     │  device_keys     │     │  collectors  │
├─────────────────┤     ├──────────────────┤     ├──────────────┤
│ id              │────▶│ id               │     │ id           │
│ collector_id    │     │ company_id       │     │ name         │
│ port            │     │ device_key       │     │ secret_key   │
│ act_key         │     │ package_name     │     │ is_active    │
│ name            │     │ log_duration     │     │ domain       │
└─────────────────┘     │ package_start_at │     │ ip           │
                        │ package_end_at   │     └──────────────┘
                        └──────────────────┘
```

## 🔄 Data Transfer Details

### 1. Company Information
```
end_customer.company        → companies.name
end_customer.id             → (reference only)
projects.activation_key     → companies.act_key
ports.port                  → companies.port
collectors.id               → companies.collector_id
```

### 2. Device Keys
```
devices.device_key          → device_keys.device_key
devices.log_duration        → device_keys.log_duration
devices.package_start_at    → device_keys.package_start_at
devices.package_end_at      → device_keys.package_end_at
companies.id                → device_keys.company_id
"Standard Package"          → device_keys.package_name (default)
```

### 3. Collector Information
```
collectors.id               → collectors.id
collectors.name             → collectors.name
collectors.secret_key       → collectors.secret_key
collectors.ip               → collectors.ip
collectors.domain           → collectors.domain
1 (active)                  → collectors.is_active
```

## 📦 Complete Response Payload

```json
{
  "status": "success",
  "message": "Activation successful",
  "timestamp": 1771096850,
  "data": {
    // Server identification
    "server_id": 123,
    "server_type": "analyzer",
    "project_id": 456,
    "project_type": "Standard",
    "activation_key": "ANL-1234-5678-9ABC",
    
    // Collector details
    "collector": {
      "id": 789,
      "name": "Collector-Singapore-01",
      "ip": "192.168.1.200",
      "domain": "https://collector.example.com",
      "secret_key": "collector_secret_key_xyz"
    },
    
    // Port configuration
    "port": {
      "id": 1,
      "port": 514
    },
    
    // Company/Customer details
    "company": {
      "id": 10,
      "name": "Alpha Corp",
      "address": "123 Main St, New York, NY",
      "contact_person": "John Doe",
      "tel": "1234567890",
      "email": "contact@alphacorp.com"
    },
    
    // Device keys array
    "devices": [
      {
        "id": 1,
        "device_key": "DEV-KEY-001-XYZ",
        "log_duration": 30,
        "package_start_at": "2026-01-01",
        "package_end_at": "2026-12-31"
      },
      {
        "id": 2,
        "device_key": "DEV-KEY-002-ABC",
        "log_duration": 30,
        "package_start_at": "2026-01-01",
        "package_end_at": "2026-12-31"
      },
      {
        "id": 3,
        "device_key": "DEV-KEY-003-DEF",
        "log_duration": 60,
        "package_start_at": "2026-01-01",
        "package_end_at": "2027-01-01"
      }
    ],
    
    // Summary
    "device_count": 10
  }
}
```

## 🎯 Client Database Population

### Step 1: Create/Update Company Record
```sql
INSERT INTO companies (collector_id, port, act_key, name)
VALUES (789, 514, 'ANL-1234-5678-9ABC', 'Alpha Corp')
ON DUPLICATE KEY UPDATE 
    port = 514,
    act_key = 'ANL-1234-5678-9ABC';
```

### Step 2: Create/Update Collector Record
```sql
INSERT INTO collectors (id, name, secret_key, is_active, domain, ip)
VALUES (789, 'Collector-Singapore-01', 'collector_secret_key_xyz', 1, 
        'https://collector.example.com', '192.168.1.200')
ON DUPLICATE KEY UPDATE 
    secret_key = 'collector_secret_key_xyz',
    is_active = 1;
```

### Step 3: Insert Device Keys
```sql
INSERT INTO device_keys 
    (company_id, device_key, package_name, log_duration, package_start_at, package_end_at)
VALUES 
    (10, 'DEV-KEY-001-XYZ', 'Standard Package', 30, '2026-01-01', '2026-12-31'),
    (10, 'DEV-KEY-002-ABC', 'Standard Package', 30, '2026-01-01', '2026-12-31'),
    (10, 'DEV-KEY-003-DEF', 'Standard Package', 60, '2026-01-01', '2027-01-01');
```

## ✅ Verification Queries

After activation, verify data was properly stored:

```sql
-- Check company record
SELECT c.*, col.name as collector_name
FROM companies c
LEFT JOIN collectors col ON c.collector_id = col.id
WHERE c.act_key = 'ANL-1234-5678-9ABC';

-- Check device keys
SELECT dk.*, c.name as company_name
FROM device_keys dk
LEFT JOIN companies c ON dk.company_id = c.id
WHERE c.act_key = 'ANL-1234-5678-9ABC';

-- Check collector
SELECT * FROM collectors WHERE id = 789;
```

## 🔐 Security Notes

All data is transferred using:
- ✅ AES-256-GCM encryption
- ✅ HMAC-SHA256 authentication
- ✅ Timestamp validation (replay attack prevention)
- ✅ Unique nonce per request

---

**Complete Data Transfer Achieved!** 🎉

All necessary information is now transferred from the admin console to the client, enabling full database population and system operation.

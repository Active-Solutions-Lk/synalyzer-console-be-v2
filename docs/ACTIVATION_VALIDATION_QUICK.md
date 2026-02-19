# Activation Validation - Quick Reference

## 🔐 How the Admin Console Validates Activation Keys

### Validation Steps (In Order)

```
┌─────────────────────────────────────────────────────────┐
│  Step 1: Check if Activation Key Exists                │
├─────────────────────────────────────────────────────────┤
│  SELECT * FROM projects WHERE activation_key = ?        │
│                                                         │
│  ❌ Not Found → ERROR: "Invalid activation key"        │
│  ✅ Found → Continue to Step 2                         │
└─────────────────────────────────────────────────────────┘
                          ▼
┌─────────────────────────────────────────────────────────┐
│  Step 2: Check if Already Activated (Analyzer)         │
├─────────────────────────────────────────────────────────┤
│  IF project.analyzer_id IS NOT NULL:                   │
│    - Get analyzer details                              │
│    - Compare IP addresses                              │
│                                                         │
│  ❌ Different IP → ERROR: "Already activated"          │
│  ✅ Same IP → Allow (re-activation)                    │
│  ✅ NULL → Allow (first activation)                    │
└─────────────────────────────────────────────────────────┘
                          ▼
┌─────────────────────────────────────────────────────────┐
│  Step 3: Process Activation                            │
├─────────────────────────────────────────────────────────┤
│  - Create/Update analyzer/collector record             │
│  - Link to project                                     │
│  - Return encrypted configuration                      │
│                                                         │
│  ✅ SUCCESS: Return activation data                    │
└─────────────────────────────────────────────────────────┘
```

## 📊 Decision Matrix

| Scenario | analyzer_id | Existing IP | Request IP | Result |
|----------|-------------|-------------|------------|--------|
| **First Activation** | `NULL` | - | `192.168.1.100` | ✅ **ALLOW** |
| **Re-activation (Same IP)** | `5` | `192.168.1.100` | `192.168.1.100` | ✅ **ALLOW** |
| **Unauthorized Use** | `5` | `192.168.1.100` | `192.168.1.200` | ❌ **BLOCK** |
| **Invalid Key** | - | - | - | ❌ **BLOCK** |

## 🎯 Real-World Examples

### ✅ Example 1: First-Time Activation
```bash
# Client runs activation
php activate.php --activation-key=ANL-1234-5678-9ABC

# Admin Console checks:
projects.analyzer_id = NULL  ← No analyzer assigned yet

# Result: ✅ ALLOWED
# Action: Create new analyzer, link to project
```

### ✅ Example 2: Server Reinstall (Same IP)
```bash
# Server crashed, admin reinstalls OS and re-activates
php activate.php --activation-key=ANL-1234-5678-9ABC

# Admin Console checks:
projects.analyzer_id = 5
analyzers[5].ip = "192.168.1.100"
request.client_ip = "192.168.1.100"  ← Same IP!

# Result: ✅ ALLOWED
# Action: Update existing analyzer record
```

### ❌ Example 3: Unauthorized Activation Attempt
```bash
# Someone tries to use the same key on a different server
php activate.php --activation-key=ANL-1234-5678-9ABC

# Admin Console checks:
projects.analyzer_id = 5
analyzers[5].ip = "192.168.1.100"
request.client_ip = "192.168.1.200"  ← Different IP!

# Result: ❌ BLOCKED
# Error: "Activation key already used by another analyzer (IP: 192.168.1.100)"
```

## 🔧 Manual Override (Admin)

If you need to reset an activation key:

```sql
-- Option 1: Clear analyzer assignment (allows new activation)
UPDATE projects 
SET analyzer_id = NULL 
WHERE activation_key = 'ANL-1234-5678-9ABC';

-- Option 2: Update IP address (if server IP changed legitimately)
UPDATE analyzers 
SET ip = '192.168.1.200' 
WHERE id = (SELECT analyzer_id FROM projects WHERE activation_key = 'ANL-1234-5678-9ABC');
```

## 📝 Error Messages

### ALREADY_ACTIVATED Error
```json
{
  "status": "error",
  "message": "Activation key already used by another analyzer (IP: 192.168.1.100)",
  "error_code": "ALREADY_ACTIVATED"
}
```

### Client Output
```bash
$ php activate.php --activation-key=ANL-1234-5678-9ABC

=== Synalyzer Activation Script ===

Client IP: 192.168.1.200
Activation Key: ANL-1234-5678-9ABC

ERROR: Activation key already used by another analyzer (IP: 192.168.1.100)

# Contact your administrator to:
# 1. Verify this is the correct activation key
# 2. Check if your IP address changed
# 3. Request a new activation key if needed
```

## 🛡️ Security Benefits

1. **Prevents Key Sharing**
   - One key = One server (by IP)
   - Unauthorized servers are blocked

2. **Allows Legitimate Re-activation**
   - Server reinstalls from same IP work
   - No need to contact support for OS upgrades

3. **Audit Trail**
   - All activation attempts are logged
   - Easy to track which IP is using which key

4. **Flexible Management**
   - Admins can manually override if needed
   - Clear error messages for troubleshooting

## 📚 Related Documentation

- **Full Details**: See `ACTIVATION_VALIDATION.md`
- **API Docs**: See `README_ACTIVATION_API.md`
- **Data Flow**: See `DATA_FLOW_DIAGRAM.md`

---

**Quick Answer**: Yes, the admin console **validates and blocks** re-activation from different IPs, but **allows** re-activation from the same IP (for server reinstalls).

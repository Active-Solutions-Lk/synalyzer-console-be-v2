<?php
/**
 * Admin Console Diagnostic Tool
 * TEMPORARY - Delete this file after debugging!
 * Access: http://your-server/synalyzer/admin_console/api/diag.php
 */

header('Content-Type: application/json');
$result = [];

// ── CRLF Detection (root cause of HTTP 500 on Linux) ──────────────────────
$baseDir = __DIR__ . '/..';
$checkFiles = [
    'activate.php'  => __FILE__,  // This file itself
    'Encryption.php' => $baseDir . '/lib/Encryption.php',
    'ApiResponse.php' => $baseDir . '/lib/ApiResponse.php',
    'Database.php'   => $baseDir . '/lib/Database.php',
    'config.php'     => $baseDir . '/config/config.php',
];
$result['crlf_check'] = [];
foreach ($checkFiles as $name => $path) {
    if (!file_exists($path)) {
        $result['crlf_check'][$name] = 'FILE NOT FOUND';
        continue;
    }
    $content = file_get_contents($path);
    $hasCrlf = (strpos($content, "\r\n") !== false);
    $hasBom  = (substr($content, 0, 3) === "\xEF\xBB\xBF");
    
    if ($hasBom) {
        $result['crlf_check'][$name] = 'HAS UTF-8 BOM — WILL BREAK HEADERS';
    } elseif ($hasCrlf) {
        $result['crlf_check'][$name] = 'CRLF (Windows line endings) — may break headers on Linux';
    } else {
        $result['crlf_check'][$name] = 'OK (LF only)';
    }
}
// ── End CRLF Detection ─────────────────────────────────────────────────────


// 1. PHP Version
$result['php_version'] = PHP_VERSION;

// 2. Required Extensions
$required = ['openssl', 'pdo', 'pdo_mysql', 'curl', 'mbstring', 'json'];
$result['extensions'] = [];
foreach ($required as $ext) {
    $result['extensions'][$ext] = extension_loaded($ext) ? 'OK' : 'MISSING';
}

// 3. OpenSSL GCM Support
$result['openssl_gcm'] = 'UNKNOWN';
if (extension_loaded('openssl')) {
    $methods = openssl_get_cipher_methods();
    $result['openssl_gcm'] = in_array('aes-256-gcm', $methods) ? 'OK' : 'NOT SUPPORTED';
    $result['openssl_version'] = OPENSSL_VERSION_TEXT;
}

// 4. File Paths
$baseDir = __DIR__ . '/..';
$paths = [
    'Encryption.php'   => $baseDir . '/lib/Encryption.php',
    'ApiResponse.php'  => $baseDir . '/lib/ApiResponse.php',
    'Database.php'     => $baseDir . '/lib/Database.php',
    'config.php'       => $baseDir . '/config/config.php',
];
$result['files'] = [];
foreach ($paths as $name => $path) {
    $result['files'][$name] = file_exists($path) ? 'OK' : 'MISSING (' . $path . ')';
}

// 5. Config Load
$result['config'] = 'UNKNOWN';
try {
    $config = require $baseDir . '/config/config.php';
    $result['config'] = 'OK';
    $result['secret_key_length'] = strlen($config['secret_key'] ?? '');
    $result['secret_key_is_default'] = ($config['secret_key'] === 'CHANGE_THIS_TO_A_STRONG_SECRET_KEY_32_CHARS_MIN');
    $result['db_host'] = $config['db']['host'] ?? 'NOT SET';
    $result['db_name'] = $config['db']['database'] ?? 'NOT SET';
    $result['db_user'] = $config['db']['username'] ?? 'NOT SET';
    $result['timezone'] = $config['api']['timezone'] ?? 'NOT SET';
} catch (Throwable $e) {
    $result['config'] = 'ERROR: ' . $e->getMessage();
}

// 6. Database Connection
$result['db_connection'] = 'UNKNOWN';
if ($result['config'] === 'OK') {
    try {
        require_once $baseDir . '/lib/Database.php';
        $db = new Database($config['db']);
        $test = $db->queryOne("SELECT 1 AS test");
        $result['db_connection'] = ($test['test'] == 1) ? 'OK' : 'FAILED - query returned unexpected result';
    } catch (Throwable $e) {
        $result['db_connection'] = 'ERROR: ' . $e->getMessage();
    }
}

// 7. Encryption Self-Test
$result['encryption'] = 'UNKNOWN';
if ($result['config'] === 'OK' && isset($config['secret_key'])) {
    try {
        require_once $baseDir . '/lib/Encryption.php';
        $enc = new Encryption($config['secret_key']);
        $testData = ['test' => 'hello', 'ts' => time()];
        $encrypted = $enc->encrypt($testData);
        $decrypted = $enc->decrypt($encrypted);
        $result['encryption'] = ($decrypted['test'] === 'hello') ? 'OK' : 'FAILED - decrypt mismatch';
    } catch (Throwable $e) {
        $result['encryption'] = 'ERROR: ' . $e->getMessage();
    }
}

// 8. Server environment
$result['server_software'] = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
$result['document_root'] = $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown';

// Output
echo json_encode($result, JSON_PRETTY_PRINT);

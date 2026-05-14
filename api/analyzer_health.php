<?php
/**
 * Admin Console - Analyzer Health Update API Endpoint
 * 
 * This endpoint receives health updates (CPU, RAM, Disk) from analyzer servers
 * and stores them in the analyzer_health table.
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Load dependencies
require_once __DIR__ . '/../lib/Encryption.php';
require_once __DIR__ . '/../lib/ApiResponse.php';
require_once __DIR__ . '/../lib/Database.php';

// Load configuration
$config = require __DIR__ . '/../config/config.php';

// Set timezone
date_default_timezone_set($config['api']['timezone']);

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiResponse::error('Method not allowed', 405, 'METHOD_NOT_ALLOWED');
}

try {
    // Initialize encryption
    $encryption = new Encryption($config['secret_key']);

    // Get raw POST data
    $rawInput = file_get_contents('php://input');

    if (empty($rawInput)) {
        ApiResponse::error('Empty request body', 400, 'EMPTY_REQUEST');
    }

    // Parse JSON
    $input = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        ApiResponse::error('Invalid JSON', 400, 'INVALID_JSON');
    }

    // Check if request is encrypted
    if (!isset($input['encrypted']) || !isset($input['payload'])) {
        ApiResponse::error('Invalid request format', 400, 'INVALID_FORMAT');
    }

    // Decrypt payload
    try {
        $requestData = $encryption->decrypt($input['payload']);
    } catch (Exception $e) {
        ApiResponse::error('Decryption failed - invalid secret key or corrupted data', 401, 'DECRYPTION_FAILED');
    }

    // Validate required fields
    $requiredFields = ['activation_key', 'cpu_load', 'ram_load', 'disk_capacity', 'timestamp', 'nonce'];
    ApiResponse::validateRequired($requestData, $requiredFields);

    // Validate timestamp (prevent replay attacks)
    if (!$encryption->validateTimestamp($requestData['timestamp'], $config['api']['max_request_age'])) {
        ApiResponse::encryptedError($encryption, 'Request expired - timestamp too old', 'TIMESTAMP_EXPIRED');
    }

    // Initialize database
    $db = new Database($config['db']);

    // Find the analyzer using the activation key
    // We join projects table with project_types to get the package name (type)
    $project = $db->queryOne(
        "SELECT p.id, p.analyzer_id, pt.type as package_name
         FROM projects p 
         JOIN project_types pt ON p.project_type_id = pt.id
         WHERE p.activation_key = ?",
        [$requestData['activation_key']]
    );

    if (!$project) {
        ApiResponse::encryptedError($encryption, 'Invalid activation key', 'INVALID_ACTIVATION_KEY');
    }

    if (!$project['analyzer_id']) {
        ApiResponse::encryptedError($encryption, 'No analyzer associated with this activation key', 'ANALYZER_NOT_FOUND');
    }

    // Insert health stats
    $db->execute(
        "INSERT INTO analyzer_health (analyzer_id, cpu_load, ram_load, disk_capacity, created_at, updated_at) 
         VALUES (?, ?, ?, ?, NOW(), NOW())",
        [
            $project['analyzer_id'],
            $requestData['cpu_load'],
            $requestData['ram_load'],
            $requestData['disk_capacity']
        ]
    );

    // Fetch ALL devices for ALL projects linked to this analyzer
    // Join project_types so each device gets its own correct package_name
    $devices = $db->query(
        "SELECT d.device_key, d.log_duration, d.package_start_at, d.package_end_at,
                pt.type AS package_name
         FROM devices d
         JOIN projects p  ON d.project_id   = p.id
         JOIN project_types pt ON p.project_type_id = pt.id
         WHERE p.analyzer_id = ?",
        [$project['analyzer_id']]
    );

    // Send encrypted success response with package sync data
    ApiResponse::encryptedSuccess(
        $encryption,
        [
            'package_sync' => $devices
        ],
        'Health stats updated successfully'
    );

} catch (Exception $e) {
    error_log('Health API error: ' . $e->getMessage());
    if (isset($encryption)) {
        ApiResponse::encryptedError($encryption, 'Internal server error', 'SERVER_ERROR');
    } else {
        ApiResponse::error('Internal server error', 500, 'SERVER_ERROR');
    }
}

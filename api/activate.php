<?php
/**
 * Admin Console - Activation API Endpoint
 * 
 * This endpoint receives activation requests from analyzer/collector servers
 * and validates them against the synalyzer_console database.
 * 
 * Request Flow:
 * 1. Client sends encrypted activation request
 * 2. Server decrypts and validates the request
 * 3. Server checks activation key and secret key
 * 4. Server updates database with server information
 * 5. Server returns encrypted configuration data
 */

// Error reporting for development (disable in production)
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

// CORS headers (adjust for production)
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
    $requiredFields = ['server_type', 'activation_key', 'client_ip', 'secret_key', 'timestamp', 'nonce'];
    ApiResponse::validateRequired($requestData, $requiredFields);

    // Validate timestamp (prevent replay attacks)
    if (!$encryption->validateTimestamp($requestData['timestamp'], $config['api']['max_request_age'])) {
        ApiResponse::encryptedError($encryption, 'Request expired - timestamp too old', 'TIMESTAMP_EXPIRED');
    }

    // Validate server type
    if (!in_array($requestData['server_type'], ['analyzer', 'collector'])) {
        ApiResponse::encryptedError($encryption, 'Invalid server type', 'INVALID_SERVER_TYPE');
    }

    // Log request
    ApiResponse::logRequest('activate', $requestData);

    // Initialize database
    $db = new Database($config['db']);

    // Validate activation key exists in projects table
    $project = $db->queryOne(
        "SELECT p.*, pt.type as project_type_name 
         FROM projects p 
         LEFT JOIN project_types pt ON p.project_type_id = pt.id
         WHERE p.activation_key = ?",
        [$requestData['activation_key']]
    );

    if (!$project) {
        ApiResponse::encryptedError($encryption, 'Invalid activation key', 'INVALID_ACTIVATION_KEY');
    }

    // Check activation status based on server type
    if ($requestData['server_type'] === 'analyzer') {

        // Check if this project already has an analyzer activated
        if ($project['analyzer_id'] !== null) {

            // Get existing analyzer details
            $existingAnalyzer = $db->queryOne(
                "SELECT * FROM analyzers WHERE id = ?",
                [$project['analyzer_id']]
            );

            // Check if it's the same IP trying to re-activate (allow re-activation from same IP)
            if ($existingAnalyzer && $existingAnalyzer['ip'] !== $requestData['client_ip']) {
                ApiResponse::encryptedError(
                    $encryption,
                    'Activation key already used by another analyzer (IP: ' . $existingAnalyzer['ip'] . ')',
                    'ALREADY_ACTIVATED'
                );
            }

            // If same IP, allow re-activation (for server reinstalls, etc.)
            // Continue with the activation process
        }

    } else if ($requestData['server_type'] === 'collector') {

        // Check if this project already has a collector activated
        // Note: For collectors, we check if the collector_id in project matches an active collector
        $existingCollector = $db->queryOne(
            "SELECT * FROM collectors WHERE id = ? AND is_active = 1",
            [$project['collector_id']]
        );

        if ($existingCollector && $existingCollector['ip'] !== $requestData['client_ip']) {
            ApiResponse::encryptedError(
                $encryption,
                'Activation key already used by another collector (IP: ' . $existingCollector['ip'] . ')',
                'ALREADY_ACTIVATED'
            );
        }
    }

    // Determine which table to update based on server_type
    if ($requestData['server_type'] === 'analyzer') {

        // Check if analyzer already exists with this IP
        $existingAnalyzer = $db->queryOne(
            "SELECT * FROM analyzers WHERE ip = ?",
            [$requestData['client_ip']]
        );

        if ($existingAnalyzer) {
            // Update existing analyzer
            $db->execute(
                "UPDATE analyzers 
                 SET status = 1, updated_at = NOW() 
                 WHERE ip = ?",
                [$requestData['client_ip']]
            );

            $analyzerId = $existingAnalyzer['id'];

        } else {
            // Insert new analyzer
            $db->execute(
                "INSERT INTO analyzers (name, ip, domain, status, created_at, updated_at) 
                 VALUES (?, ?, ?, 1, NOW(), NOW())",
                [
                    $requestData['server_name'] ?? 'Analyzer-' . substr($requestData['client_ip'], -6),
                    $requestData['client_ip'],
                    $requestData['domain'] ?? null
                ]
            );

            $analyzerId = $db->lastInsertId();
        }

        // Update project with analyzer_id
        $db->execute(
            "UPDATE projects SET analyzer_id = ?, updated_at = NOW() WHERE id = ?",
            [$analyzerId, $project['id']]
        );

        // Get collector information for this project
        $collector = $db->queryOne(
            "SELECT * FROM collectors WHERE id = ?",
            [$project['collector_id']]
        );

        // Get port information
        $port = $db->queryOne(
            "SELECT * FROM ports WHERE id = ?",
            [$project['port_id']]
        );

        // Get company/customer information
        $company = null;
        if ($project['end_customer_id']) {
            $company = $db->queryOne(
                "SELECT * FROM end_customer WHERE id = ?",
                [$project['end_customer_id']]
            );
        }

        // Get device keys for this project
        $devices = $db->query(
            "SELECT * FROM devices WHERE project_id = ?",
            [$project['id']]
        );

        // Prepare response data
        $responseData = [
            'server_id' => $analyzerId,
            'server_type' => 'analyzer',
            'project_id' => $project['id'],
            'project_type' => $project['project_type_name'],
            'activation_key' => $requestData['activation_key'],
            'collector' => [
                'id' => $collector['id'],
                'name' => $collector['name'],
                'ip' => $collector['ip'],
                'domain' => $collector['domain'],
                'secret_key' => $collector['secret_key'],
            ],
            'port' => [
                'id' => $port['id'],
                'port' => $port['port'],
            ],
            'company' => $company ? [
                'id' => $company['id'],
                'name' => $company['company'],
                'address' => $company['address'],
                'contact_person' => $company['contact_person'],
                'tel' => $company['tel'],
                'email' => $company['email'],
            ] : null,
            'devices' => array_map(function ($device) {
                return [
                    'id' => $device['id'],
                    'device_key' => $device['device_key'],
                    'log_duration' => $device['log_duration'],
                    'package_start_at' => $device['package_start_at'],
                    'package_end_at' => $device['package_end_at'],
                ];
            }, $devices),
            'device_count' => $project['device_count'],
        ];

    } else if ($requestData['server_type'] === 'collector') {

        // Check if collector already exists with this IP
        $existingCollector = $db->queryOne(
            "SELECT * FROM collectors WHERE ip = ?",
            [$requestData['client_ip']]
        );

        if ($existingCollector) {
            // Update existing collector
            $db->execute(
                "UPDATE collectors 
                 SET is_active = 1, updated_at = NOW() 
                 WHERE ip = ?",
                [$requestData['client_ip']]
            );

            $collectorId = $existingCollector['id'];

        } else {
            // Insert new collector
            $secretKey = Encryption::generateNonce(16);

            $db->execute(
                "INSERT INTO collectors (name, ip, domain, secret_key, is_active, created_at, updated_at) 
                 VALUES (?, ?, ?, ?, 1, NOW(), NOW())",
                [
                    $requestData['server_name'] ?? 'Collector-' . substr($requestData['client_ip'], -6),
                    $requestData['client_ip'],
                    $requestData['domain'] ?? null,
                    $secretKey
                ]
            );

            $collectorId = $db->lastInsertId();
        }

        // Update project with collector_id
        $db->execute(
            "UPDATE projects SET collector_id = ?, updated_at = NOW() WHERE id = ?",
            [$collectorId, $project['id']]
        );

        // Get updated collector info
        $collector = $db->queryOne(
            "SELECT * FROM collectors WHERE id = ?",
            [$collectorId]
        );

        // Get port information
        $port = $db->queryOne(
            "SELECT * FROM ports WHERE id = ?",
            [$project['port_id']]
        );

        // Get company/customer information
        $company = null;
        if ($project['end_customer_id']) {
            $company = $db->queryOne(
                "SELECT * FROM end_customer WHERE id = ?",
                [$project['end_customer_id']]
            );
        }

        // Get device keys for this project
        $devices = $db->query(
            "SELECT * FROM devices WHERE project_id = ?",
            [$project['id']]
        );

        // Prepare response data
        $responseData = [
            'server_id' => $collectorId,
            'server_type' => 'collector',
            'project_id' => $project['id'],
            'project_type' => $project['project_type_name'],
            'activation_key' => $requestData['activation_key'],
            'collector' => [
                'id' => $collector['id'],
                'name' => $collector['name'],
                'ip' => $collector['ip'] ?? $requestData['client_ip'],
                'domain' => $collector['domain'],
                'secret_key' => $collector['secret_key'],
            ],
            'port' => [
                'id' => $port['id'],
                'port' => $port['port'],
            ],
            'company' => $company ? [
                'id' => $company['id'],
                'name' => $company['company'],
                'address' => $company['address'],
                'contact_person' => $company['contact_person'],
                'tel' => $company['tel'],
                'email' => $company['email'],
            ] : null,
            'devices' => array_map(function ($device) {
                return [
                    'id' => $device['id'],
                    'device_key' => $device['device_key'],
                    'log_duration' => $device['log_duration'],
                    'package_start_at' => $device['package_start_at'],
                    'package_end_at' => $device['package_end_at'],
                ];
            }, $devices),
            'device_count' => $project['device_count'],
        ];
    }

    // Send encrypted success response
    ApiResponse::encryptedSuccess(
        $encryption,
        $responseData,
        'Activation successful'
    );

} catch (Exception $e) {
    error_log('Activation API error: ' . $e->getMessage());

    if (isset($encryption)) {
        ApiResponse::encryptedError($encryption, 'Internal server error', 'SERVER_ERROR');
    } else {
        ApiResponse::error('Internal server error', 500, 'SERVER_ERROR');
    }
}

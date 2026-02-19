<?php
/**
 * API Response Handler
 * Standardized JSON responses for the Synalyzer API
 */

class ApiResponse
{

    /**
     * Send a success response
     * 
     * @param mixed $data Response data
     * @param string $message Success message
     * @param int $httpCode HTTP status code (default: 200)
     */
    public static function success($data = null, $message = 'Success', $httpCode = 200)
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');

        $response = [
            'status' => 'success',
            'message' => $message,
            'timestamp' => time(),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send an error response
     * 
     * @param string $message Error message
     * @param int $httpCode HTTP status code (default: 400)
     * @param string $errorCode Optional error code for client handling
     */
    public static function error($message = 'An error occurred', $httpCode = 400, $errorCode = null)
    {
        http_response_code($httpCode);
        header('Content-Type: application/json');

        $response = [
            'status' => 'error',
            'message' => $message,
            'timestamp' => time(),
        ];

        if ($errorCode !== null) {
            $response['error_code'] = $errorCode;
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send an encrypted success response
     * 
     * @param Encryption $encryption Encryption instance
     * @param mixed $data Response data
     * @param string $message Success message
     */
    public static function encryptedSuccess($encryption, $data = null, $message = 'Success')
    {
        try {
            $response = [
                'status' => 'success',
                'message' => $message,
                'timestamp' => time(),
            ];

            if ($data !== null) {
                $response['data'] = $data;
            }

            $encrypted = $encryption->encrypt($response);

            http_response_code(200);
            header('Content-Type: application/json');

            echo json_encode([
                'encrypted' => true,
                'payload' => $encrypted
            ], JSON_PRETTY_PRINT);

            exit;

        } catch (Exception $e) {
            self::error('Failed to encrypt response', 500, 'ENCRYPTION_ERROR');
        }
    }

    /**
     * Send an encrypted error response
     * 
     * @param Encryption $encryption Encryption instance
     * @param string $message Error message
     * @param string $errorCode Optional error code
     */
    public static function encryptedError($encryption, $message = 'An error occurred', $errorCode = null)
    {
        try {
            $response = [
                'status' => 'error',
                'message' => $message,
                'timestamp' => time(),
            ];

            if ($errorCode !== null) {
                $response['error_code'] = $errorCode;
            }

            $encrypted = $encryption->encrypt($response);

            http_response_code(400);
            header('Content-Type: application/json');

            echo json_encode([
                'encrypted' => true,
                'payload' => $encrypted
            ], JSON_PRETTY_PRINT);

            exit;

        } catch (Exception $e) {
            self::error('Failed to encrypt error response', 500, 'ENCRYPTION_ERROR');
        }
    }

    /**
     * Validate required fields in request data
     * 
     * @param array $data Request data
     * @param array $requiredFields List of required field names
     * @return bool True if all fields present, sends error response otherwise
     */
    public static function validateRequired($data, $requiredFields)
    {
        $missing = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            self::error(
                'Missing required fields: ' . implode(', ', $missing),
                400,
                'MISSING_FIELDS'
            );
            return false;
        }

        return true;
    }

    /**
     * Log API request for debugging/auditing
     * 
     * @param string $endpoint Endpoint name
     * @param array $data Request data (sensitive data will be masked)
     */
    public static function logRequest($endpoint, $data = [])
    {
        // Mask sensitive fields
        $masked = $data;
        $sensitiveFields = ['secret_key', 'password', 'activation_key'];

        foreach ($sensitiveFields as $field) {
            if (isset($masked[$field])) {
                $masked[$field] = '***MASKED***';
            }
        }

        $logEntry = sprintf(
            "[%s] %s - IP: %s - Data: %s\n",
            date('Y-m-d H:i:s'),
            $endpoint,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            json_encode($masked)
        );

        error_log($logEntry);
    }
}

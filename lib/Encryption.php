<?php
/**
 * Encryption Library for Synalyzer Activation API
 * Uses AES-256-GCM for encryption and HMAC-SHA256 for authentication
 * 
 * Security Features:
 * - AES-256-GCM encryption with authenticated encryption
 * - HMAC-SHA256 for message authentication
 * - Nonce/IV generation for replay attack prevention
 * - Timestamp validation for time-based attacks
 */

class Encryption {
    
    private const CIPHER_METHOD = 'aes-256-gcm';
    private const HASH_ALGO = 'sha256';
    private const TAG_LENGTH = 16;
    
    private $encryptionKey;
    private $authKey;
    
    /**
     * Initialize encryption with a master secret key
     * 
     * @param string $secretKey Master secret key (should be 32+ characters)
     */
    public function __construct($secretKey) {
        // Derive two separate keys from the master secret
        $this->encryptionKey = hash_pbkdf2('sha256', $secretKey, 'encryption_salt', 10000, 32, true);
        $this->authKey = hash_pbkdf2('sha256', $secretKey, 'auth_salt', 10000, 32, true);
    }
    
    /**
     * Encrypt data with AES-256-GCM
     * 
     * @param mixed $data Data to encrypt (will be JSON encoded)
     * @return string Base64 encoded encrypted payload
     * @throws Exception If encryption fails
     */
    public function encrypt($data) {
        try {
            // Convert data to JSON
            $plaintext = json_encode($data);
            
            // Generate random IV (12 bytes for GCM)
            $iv = random_bytes(12);
            
            // Encrypt with AES-256-GCM
            $tag = '';
            $ciphertext = openssl_encrypt(
                $plaintext,
                self::CIPHER_METHOD,
                $this->encryptionKey,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
                '',
                self::TAG_LENGTH
            );
            
            if ($ciphertext === false) {
                throw new Exception('Encryption failed');
            }
            
            // Combine IV + Tag + Ciphertext
            $encrypted = $iv . $tag . $ciphertext;
            
            // Generate HMAC for additional authentication
            $hmac = hash_hmac(self::HASH_ALGO, $encrypted, $this->authKey, true);
            
            // Combine HMAC + Encrypted data
            $payload = $hmac . $encrypted;
            
            // Return base64 encoded
            return base64_encode($payload);
            
        } catch (Exception $e) {
            error_log('Encryption error: ' . $e->getMessage());
            throw new Exception('Encryption failed');
        }
    }
    
    /**
     * Decrypt data encrypted with encrypt()
     * 
     * @param string $encryptedData Base64 encoded encrypted payload
     * @return mixed Decrypted data (JSON decoded)
     * @throws Exception If decryption or authentication fails
     */
    public function decrypt($encryptedData) {
        try {
            // Decode base64
            $payload = base64_decode($encryptedData, true);
            
            if ($payload === false) {
                throw new Exception('Invalid base64 encoding');
            }
            
            // Extract HMAC (32 bytes for SHA256)
            $hmacLength = 32;
            if (strlen($payload) < $hmacLength + 12 + self::TAG_LENGTH) {
                throw new Exception('Invalid payload length');
            }
            
            $hmac = substr($payload, 0, $hmacLength);
            $encrypted = substr($payload, $hmacLength);
            
            // Verify HMAC
            $expectedHmac = hash_hmac(self::HASH_ALGO, $encrypted, $this->authKey, true);
            
            if (!hash_equals($expectedHmac, $hmac)) {
                throw new Exception('HMAC verification failed - data may be tampered');
            }
            
            // Extract IV, Tag, and Ciphertext
            $iv = substr($encrypted, 0, 12);
            $tag = substr($encrypted, 12, self::TAG_LENGTH);
            $ciphertext = substr($encrypted, 12 + self::TAG_LENGTH);
            
            // Decrypt
            $plaintext = openssl_decrypt(
                $ciphertext,
                self::CIPHER_METHOD,
                $this->encryptionKey,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );
            
            if ($plaintext === false) {
                throw new Exception('Decryption failed - invalid data or key');
            }
            
            // Decode JSON
            $data = json_decode($plaintext, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data');
            }
            
            return $data;
            
        } catch (Exception $e) {
            error_log('Decryption error: ' . $e->getMessage());
            throw new Exception('Decryption failed');
        }
    }
    
    /**
     * Validate timestamp to prevent replay attacks
     * 
     * @param int $timestamp Unix timestamp from request
     * @param int $maxAge Maximum age in seconds (default: 300 = 5 minutes)
     * @return bool True if timestamp is valid
     */
    public function validateTimestamp($timestamp, $maxAge = 300) {
        $now = time();
        $diff = abs($now - $timestamp);
        
        return $diff <= $maxAge;
    }
    
    /**
     * Generate a secure random nonce
     * 
     * @param int $length Length of nonce in bytes
     * @return string Hex encoded nonce
     */
    public static function generateNonce($length = 16) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Generate a secure activation key
     * 
     * @param string $prefix Optional prefix (e.g., 'ANL' for analyzer, 'COL' for collector)
     * @return string Formatted activation key (e.g., ANL-XXXX-XXXX-XXXX)
     */
    public static function generateActivationKey($prefix = '') {
        $part1 = strtoupper(bin2hex(random_bytes(2)));
        $part2 = strtoupper(bin2hex(random_bytes(2)));
        $part3 = strtoupper(bin2hex(random_bytes(2)));
        
        if ($prefix) {
            return strtoupper($prefix) . '-' . $part1 . '-' . $part2 . '-' . $part3;
        }
        
        return $part1 . '-' . $part2 . '-' . $part3;
    }
    
    /**
     * Hash a password or secret using bcrypt
     * 
     * @param string $password Password to hash
     * @return string Hashed password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify a password against a hash
     * 
     * @param string $password Plain password
     * @param string $hash Hashed password
     * @return bool True if password matches
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

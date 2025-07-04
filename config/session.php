<?php
/**
 * Session Management Configuration
 * Handles all session-related functionality for the Proteq API
 * Token-based authentication without cookies
 */

// Disable cookie-based sessions
ini_set('session.use_cookies', 0);
ini_set('session.use_only_cookies', 0);
ini_set('session.use_trans_sid', 0);

// Session timeout (30 minutes)
ini_set('session.gc_maxlifetime', 1800);

class SessionManager {
    // Remove static $sessions
    // private static $sessions = [];

    // --- Database connection helper ---
    private static function db() {
        // Use your actual DB credentials here
        static $pdo = null;
        if ($pdo === null) {
            $pdo = new PDO('mysql:host=localhost;dbname=proteq_db', 'root', '');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return $pdo;
    }

    private static function loadSession($token) {
        $stmt = self::db()->prepare('SELECT data FROM sessions WHERE token = ?');
        $stmt->execute([$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? json_decode($row['data'], true) : null;
    }

    private static function saveSession($token, $session) {
        $stmt = self::db()->prepare('REPLACE INTO sessions (token, data, last_activity) VALUES (?, ?, ?)');
        $stmt->execute([$token, json_encode($session), $session['last_activity']]);
    }

    private static function deleteSession($token) {
        $stmt = self::db()->prepare('DELETE FROM sessions WHERE token = ?');
        $stmt->execute([$token]);
    }

    /**
     * Generate a unique session token
     * @return string
     */
    public static function generateToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Start session with token
     * @param string $token
     * @return bool
     */
    public static function startSession($token = null) {
        if ($token && isset(self::$sessions[$token])) {
            return true;
        }
        return false;
    }
    
    /**
     * Check if user is logged in
     * @param string $token
     * @return bool
     */
    public static function isLoggedIn($token = null) {
        if (!$token) return false;
        $session = self::loadSession($token);
        return $session && (isset($session['user_id']) || isset($session['staff_id']));
    }
    
    /**
     * Get current user ID
     * @param string $token
     * @return int|null
     */
    public static function getCurrentUserId($token = null) {
        if (!$token) return null;
        $session = self::loadSession($token);
        return $session['user_id'] ?? $session['staff_id'] ?? null;
    }
    
    /**
     * Get current user type
     * @param string $token
     * @return string|null
     */
    public static function getCurrentUserType($token = null) {
        if (!$token) return null;
        $session = self::loadSession($token);
        return $session['user_type'] ?? $session['role'] ?? null;
    }
    
    /**
     * Get current user email
     * @param string $token
     * @return string|null
     */
    public static function getCurrentUserEmail($token = null) {
        if (!$token) return null;
        $session = self::loadSession($token);
        return $session['email'] ?? null;
    }
    
    /**
     * Get current user name
     * @param string $token
     * @return string|null
     */
    public static function getCurrentUserName($token = null) {
        if (!$token) return null;
        $session = self::loadSession($token);
        if (isset($session['first_name']) && isset($session['last_name'])) {
            return $session['first_name'] . ' ' . $session['last_name'];
        }
        return $session['name'] ?? null;
    }
    
    /**
     * Set user session data
     * @param array $userData
     * @return string
     */
    public static function setUserSession($userData) {
        $token = self::generateToken();
        $session = [
            'user_id' => $userData['user_id'] ?? null,
            'user_type' => $userData['user_type'] ?? null,
            'email' => $userData['email'] ?? null,
            'first_name' => $userData['first_name'] ?? null,
            'last_name' => $userData['last_name'] ?? null,
            'department' => $userData['department'] ?? null,
            'college' => $userData['college'] ?? null,
            'status' => $userData['status'] ?? null,
            'login_time' => time(),
            'last_activity' => time()
        ];
        self::saveSession($token, $session);
        return $token;
    }
    
    /**
     * Set staff session data
     * @param array $staffData
     * @return string
     */
    public static function setStaffSession($staffData) {
        $token = self::generateToken();
        $session = [
            'staff_id' => $staffData['staff_id'] ?? null,
            'role' => $staffData['role'] ?? null,
            'user_type' => $staffData['role'] ?? 'staff',
            'email' => $staffData['email'] ?? null,
            'name' => $staffData['name'] ?? null,
            'availability' => $staffData['availability'] ?? null,
            'status' => $staffData['status'] ?? null,
            'login_time' => time(),
            'last_activity' => time()
        ];
        self::saveSession($token, $session);
        return $token;
    }
    
    /**
     * Update last activity time
     * @param string $token
     */
    public static function updateActivity($token = null) {
        if (!$token) return;
        $session = self::loadSession($token);
        if ($session) {
            $session['last_activity'] = time();
            self::saveSession($token, $session);
        }
    }
    
    /**
     * Check if session has expired
     * @param string $token
     * @param int $timeoutMinutes
     * @return bool
     */
    public static function isSessionExpired($token = null, $timeoutMinutes = 30) {
        if (!$token) return true;
        $session = self::loadSession($token);
        if (!$session || !isset($session['last_activity'])) return true;
        $timeout = $timeoutMinutes * 60;
        return (time() - $session['last_activity']) > $timeout;
    }
    
    /**
     * Destroy session and logout user
     * @param string $token
     */
    public static function logout($token = null) {
        if ($token) self::deleteSession($token);
    }
    
    /**
     * Require authentication - return error if not logged in
     * @param string $token
     * @param bool $returnJson
     * @return array|void
     */
    public static function requireAuth($token = null, $returnJson = true) {
        if (!self::isLoggedIn($token)) {
            if ($returnJson) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Authentication required. Please login.'
                ]);
                exit();
            } else {
                return [
                    'success' => false,
                    'message' => 'Authentication required'
                ];
            }
        }
        
        // Check if session has expired
        if (self::isSessionExpired($token)) {
            if ($returnJson) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Session expired. Please login again.'
                ]);
                exit();
            } else {
                return [
                    'success' => false,
                    'message' => 'Session expired'
                ];
            }
        }
        
        // Update last activity
        self::updateActivity($token);
        
        if (!$returnJson) {
            $session = self::loadSession($token);
            return [
                'success' => true,
                'user_id' => $session['user_id'] ?? $session['staff_id'] ?? null,
                'user_type' => $session['user_type'] ?? $session['role'] ?? null
            ];
        }
    }
    
    /**
     * Get current user data as array
     * @param string $token
     * @return array
     */
    public static function getCurrentUserData($token = null) {
        if (!$token) return [];
        $session = self::loadSession($token);
        if (!$session) return [];
        return [
            'user_id' => $session['user_id'] ?? $session['staff_id'] ?? null,
            'user_type' => $session['user_type'] ?? $session['role'] ?? null,
            'email' => $session['email'] ?? null,
            'name' => isset($session['first_name']) && isset($session['last_name']) ? $session['first_name'] . ' ' . $session['last_name'] : ($session['name'] ?? null),
            'login_time' => $session['login_time'] ?? null,
            'last_activity' => $session['last_activity'] ?? null
        ];
    }
    
    /**
     * Get token from Authorization header
     * @return string|null
     */
    public static function getTokenFromHeader() {
        // Try getallheaders() first
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

        // Fallback to $_SERVER if not found
        if (!$authHeader) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
        }

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
?> 
<?php
require_once __DIR__ . '/../config/database.php';

class UserActivity {
    private $conn;
    private $table_name = 'user_activity_log';

    public function __construct() {
        $this->conn = getDB();
    }

    /**
     * Log user activity
     */
    public function logActivity($user_id, $activity_type, $ip_address = null, $user_agent = null, $activity_details = null) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, activity_type, ip_address, user_agent, activity_details) 
                  VALUES (:user_id, :activity_type, :ip_address, :user_agent, :activity_details)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':activity_type', $activity_type);
        $stmt->bindParam(':ip_address', $ip_address);
        $stmt->bindParam(':user_agent', $user_agent);
        $stmt->bindParam(':activity_details', $activity_details);

        return $stmt->execute();
    }

    /**
     * Log login activity
     */
    public function logLogin($user_id, $ip_address = null, $user_agent = null) {
        $activity_details = json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => 'user_login'
        ]);
        
        return $this->logActivity($user_id, 'login', $ip_address, $user_agent, $activity_details);
    }

    /**
     * Log logout activity
     */
    public function logLogout($user_id, $ip_address = null, $user_agent = null) {
        $activity_details = json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => 'user_logout'
        ]);
        
        return $this->logActivity($user_id, 'logout', $ip_address, $user_agent, $activity_details);
    }

    /**
     * Get recent activities for admin dashboard
     */
    public function getRecentActivities($limit = 20) {
        $query = "SELECT ual.*, u.first_name, u.last_name, u.email, d.dept_name, r.role_name
                  FROM " . $this->table_name . " ual
                  LEFT JOIN users u ON ual.user_id = u.id
                  LEFT JOIN departments d ON u.department_id = d.id
                  LEFT JOIN roles r ON u.role_id = r.id
                  ORDER BY ual.created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get user's recent activities
     */
    public function getUserActivities($user_id, $limit = 10) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get activity statistics
     */
    public function getActivityStats($days = 30) {
        $query = "SELECT 
                    COUNT(*) as total_activities,
                    COUNT(CASE WHEN activity_type = 'login' THEN 1 END) as total_logins,
                    COUNT(CASE WHEN activity_type = 'logout' THEN 1 END) as total_logouts,
                    COUNT(DISTINCT user_id) as unique_users
                  FROM " . $this->table_name . " 
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Format activity type for display
     */
    public function formatActivityType($activity_type) {
        switch ($activity_type) {
            case 'login':
                return 'Logged In';
            case 'logout':
                return 'Logged Out';
            case 'password_change':
                return 'Changed Password';
            case 'profile_update':
                return 'Updated Profile';
            default:
                return ucfirst($activity_type);
        }
    }

    /**
     * Get activity icon
     */
    public function getActivityIcon($activity_type) {
        switch ($activity_type) {
            case 'login':
                return '<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>';
            case 'logout':
                return '<svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>';
            case 'password_change':
                return '<svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>';
            case 'profile_update':
                return '<svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>';
            default:
                return '<svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>';
        }
    }
}
?>

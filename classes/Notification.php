<?php
require_once __DIR__ . '/../config/database.php';

class Notification {
    private $conn;
    private $table_name = 'notifications';

    public function __construct() {
        $this->conn = getDB();
    }

    /**
     * Get notifications for a user
     */
    public function getUserNotifications($user_id, $limit = 10, $unread_only = false) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE user_id = :user_id";
        
        if ($unread_only) {
            $query .= " AND is_read = FALSE";
        }
        
        $query .= " ORDER BY created_at DESC LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all notifications (for admin)
     */
    public function getAllNotifications($limit = 20) {
        $query = "SELECT n.*, u.first_name, u.last_name, u.email
                  FROM " . $this->table_name . " n
                  LEFT JOIN users u ON n.user_id = u.id
                  ORDER BY n.created_at DESC LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create new notification
     */
    public function createNotification($user_id, $title, $message, $type = 'info') {
        $query = "INSERT INTO " . $this->table_name . " 
                  (user_id, title, message, type) 
                  VALUES (:user_id, :title, :message, :type)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':type', $type);

        return $stmt->execute();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notification_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_read = TRUE, read_at = NOW() 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $notification_id);

        return $stmt->execute();
    }

    /**
     * Get unread notification count
     */
    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " 
                  WHERE user_id = :user_id AND is_read = FALSE";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($user_id) {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_read = TRUE, read_at = NOW() 
                  WHERE user_id = :user_id AND is_read = FALSE";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }
}
?>

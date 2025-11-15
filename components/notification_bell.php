<?php
// Notification Bell Component
// Usage: include this file in any page that needs a notification bell
// Requires: $unreadCount variable to be set before including this component
?>

<div class="relative" x-data="{ open: false }">
    <!-- Notification Bell Button -->
    <button @click="open = !open" class="relative p-3 text-gray-600 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 rounded-full transition-colors duration-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
        </svg>
        <!-- Red dot for unread notifications -->
        <?php if (isset($unreadCount) && $unreadCount > 0): ?>
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium animate-pulse">
                <?php echo $unreadCount > 9 ? '9+' : $unreadCount; ?>
            </span>
        <?php endif; ?>
    </button>
    
    <!-- Notification Dropdown -->
    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                <?php if (isset($unreadCount) && $unreadCount > 0): ?>
                    <button onclick="markAllAsRead()" class="text-sm text-red-600 hover:text-red-800">Mark all as read</button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="max-h-96 overflow-y-auto" id="notificationsContainer">
            <?php if (empty($notifications)): ?>
                <div class="p-4 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    <p>No notifications</p>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer" onclick="markAsRead(<?php echo $notif['id']; ?>)">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <?php
                                $iconClass = '';
                                $bgClass = '';
                                switch ($notif['type']) {
                                    case 'success':
                                        $iconClass = 'text-green-600';
                                        $bgClass = 'bg-green-100';
                                        break;
                                    case 'warning':
                                        $iconClass = 'text-yellow-600';
                                        $bgClass = 'bg-yellow-100';
                                        break;
                                    case 'error':
                                        $iconClass = 'text-red-600';
                                        $bgClass = 'bg-red-100';
                                        break;
                                    default:
                                        $iconClass = 'text-blue-600';
                                        $bgClass = 'bg-blue-100';
                                }
                                ?>
                                <div class="w-8 h-8 <?php echo $bgClass; ?> rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 <?php echo $iconClass; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($notif['title']); ?></p>
                                <p class="text-sm text-gray-500 truncate"><?php echo htmlspecialchars($notif['message']); ?></p>
                                <p class="text-xs text-gray-400 mt-1"><?php echo date('M j, Y g:i A', strtotime($notif['created_at'])); ?></p>
                            </div>
                            <?php if (!$notif['is_read']): ?>
                                <div class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0"></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="p-4 border-t border-gray-200">
            <div class="flex justify-between items-center">
                <?php
                // Determine the correct notifications page based on user role
                $notifications_page = 'notifications.php'; // Default for department users
                if (isset($_SESSION['user_role'])) {
                    if (in_array($_SESSION['user_role'], ['budget', 'school_admin'])) {
                        $notifications_page = 'notifications_admin.php';
                    }
                }
                ?>
                <a href="<?php echo $notifications_page; ?>" class="text-sm text-red-600 hover:text-red-800">View all notifications</a>
                <?php if (!empty($notifications)): ?>
                    <button onclick="clearNotifications()" class="text-sm text-gray-500 hover:text-gray-700">Clear all</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Check if notifications are cleared on page load
document.addEventListener('DOMContentLoaded', function() {
    const isCleared = localStorage.getItem('notificationsCleared');
    if (isCleared === 'true') {
        clearNotificationsUI();
    }
});

function markAsRead(notificationId) {
    // AJAX call to mark notification as read
    fetch('../ajax/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ notification_id: notificationId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI - remove red dot and mark as read
            const notificationElement = document.querySelector(`[onclick="markAsRead(${notificationId})"]`);
            if (notificationElement) {
                const redDot = notificationElement.querySelector('.bg-red-500');
                if (redDot) {
                    redDot.remove();
                }
                notificationElement.classList.remove('bg-blue-50');
            }
            // Update unread count
            updateUnreadCount();
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAllAsRead() {
    // AJAX call to mark all notifications as read
    fetch('../ajax/mark_all_notifications_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove all red dots and update UI
            document.querySelectorAll('.bg-red-500').forEach(dot => dot.remove());
            document.querySelectorAll('[onclick^="markAsRead"]').forEach(notif => {
                notif.classList.remove('bg-blue-50');
            });
            // Update unread count
            updateUnreadCount();
            // Hide mark all as read button
            const markAllButton = document.querySelector('[onclick="markAllAsRead()"]');
            if (markAllButton) markAllButton.style.display = 'none';
        }
    })
    .catch(error => console.error('Error:', error));
}

function clearNotifications() {
    if (confirm('Are you sure you want to clear all notifications from this dropdown?')) {
        // Store cleared state in localStorage
        localStorage.setItem('notificationsCleared', 'true');
        
        // Clear the notifications dropdown content
        clearNotificationsUI();
    }
}

function clearNotificationsUI() {
    const notificationsContainer = document.getElementById('notificationsContainer');
    if (notificationsContainer) {
        notificationsContainer.innerHTML = `
            <div class="p-4 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                <p>No notifications</p>
            </div>
        `;
    }
    
    // Hide the "Mark all as read" and "Clear all" buttons
    const markAllButton = document.querySelector('[onclick="markAllAsRead()"]');
    const clearButton = document.querySelector('[onclick="clearNotifications()"]');
    if (markAllButton) markAllButton.style.display = 'none';
    if (clearButton) clearButton.style.display = 'none';
    
    // Hide the red dot
    const redDot = document.querySelector('.bg-red-500');
    if (redDot) redDot.style.display = 'none';
}

function updateUnreadCount() {
    // Update the red dot count in the notification bell
    const redDot = document.querySelector('.bg-red-500');
    if (redDot) {
        const currentCount = parseInt(redDot.textContent) || 0;
        const newCount = Math.max(0, currentCount - 1);
        if (newCount === 0) {
            redDot.style.display = 'none';
        } else {
            redDot.textContent = newCount > 9 ? '9+' : newCount;
        }
    }
}

// Function to reset cleared state (for testing or if needed)
function resetNotificationsState() {
    localStorage.removeItem('notificationsCleared');
    location.reload();
}
</script>

<!-- Include Alpine.js for dropdown functionality -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

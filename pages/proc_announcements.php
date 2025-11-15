<?php
session_start();

// Require login; restrict to procurement role; redirect others to their dashboards
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'procurement') {
    switch ($_SESSION['user_role'] ?? '') {
        case 'budget':
            header('Location: ./admin_dashboard.php');
            break;
        case 'school_admin':
            header('Location: ./school_admin_dashboard.php');
            break;
        case 'offices':
            header('Location: ./dept_dashboard.php');
            break;
        default:
            header('Location: ../login.php');
            break;
    }
    exit;
}

$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Procurement';
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$officeLabel = 'Procurement Office';

// Sample announcements data - in a real system this would come from a database
$announcements = [
    [
        'id' => 1,
        'title' => 'New Procurement Guidelines',
        'content' => 'Please review the updated procurement guidelines for fiscal year 2024. All departments must follow the new procedures when submitting PPMP and LIB documents.',
        'date' => '2024-01-15',
        'priority' => 'high'
    ],
    [
        'id' => 2,
        'title' => 'Budget Allocation Update',
        'content' => 'The budget allocation for Q1 2024 has been finalized. All departments can now proceed with their procurement activities according to their allocated budgets.',
        'date' => '2024-01-10',
        'priority' => 'medium'
    ],
    [
        'id' => 3,
        'title' => 'System Maintenance Notice',
        'content' => 'The BudgetTrack system will undergo maintenance on January 20, 2024 from 2:00 AM to 4:00 AM. Please save your work before this time.',
        'date' => '2024-01-08',
        'priority' => 'low'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetTrack - Procurement Announcements</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        maroon: '#800000',
                        'maroon-dark': '#5a0000',
                        'maroon-light': '#a00000',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-inter">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg border-r border-gray-200 relative">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-maroon">BudgetTrack</h2>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($officeLabel); ?></p>
            </div>
            
            <nav class="mt-6">
                <a href="proc_dashboard.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                    </svg>
                    Dashboard (Budget Overview)
                </a>
                <a href="submit_documents.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Submit PPMP & LIB
                </a>
                <a href="proc_track_requests.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Track Requests
                </a>
                <a href="proc_reports.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Reports
                </a>
                <a href="proc_notifications.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 102.828 2.828l6.414 6.414a2 2 0 01-2.828 2.828L4.828 7z"></path>
                    </svg>
                    Notifications
                </a>
                <a href="proc_announcements.php" class="flex items-center px-6 py-3 text-maroon bg-red-50 border-r-4 border-maroon">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Announcements
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-maroon">Procurement Announcements</h1>
                        <p class="text-gray-600">Welcome, <?php echo htmlspecialchars($username); ?></p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <button onclick="toggleProfileDropdown()" class="flex items-center space-x-3 bg-gray-50 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-10 h-10 bg-maroon rounded-full flex items-center justify-center text-white font-semibold">
                                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($username); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($userEmail); ?></div>
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Profile Dropdown -->
                            <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 hidden">
                                <div class="py-1">
                                    <a href="profile.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Profile
                                    </a>
                                    <a href="change_password.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Change Password
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                    <button onclick="confirmLogout()" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Logout
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="flex-1 p-6">
                <!-- Create Announcement Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-maroon">Create New Announcement</h2>
                        <button onclick="toggleCreateForm()" class="px-4 py-2 bg-maroon text-white rounded-lg hover:bg-maroon-dark transition-colors">
                            <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            New Announcement
                        </button>
                    </div>
                    
                    <!-- Create Announcement Form -->
                    <div id="createForm" class="hidden">
                        <form class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                                    <input type="text" id="announcementTitle" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon focus:border-transparent" placeholder="Enter announcement title">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Priority *</label>
                                    <select id="announcementPriority" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon focus:border-transparent">
                                        <option value="">Select Priority</option>
                                        <option value="high">High Priority</option>
                                        <option value="medium">Medium Priority</option>
                                        <option value="low">Low Priority</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Content *</label>
                                <textarea id="announcementContent" required rows="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon focus:border-transparent resize-none" placeholder="Enter announcement content..."></textarea>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="cancelCreate()" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                                    Cancel
                                </button>
                                <button type="button" onclick="createAnnouncement()" class="px-6 py-3 bg-maroon text-white rounded-lg hover:bg-maroon-dark transition-colors">
                                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    Publish Announcement
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Announcements Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-maroon">Important Announcements</h2>
                        <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                            <?php echo count($announcements); ?> announcements
                        </span>
                    </div>
                    
                    <div class="space-y-6">
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="border-l-4 <?php 
                                switch($announcement['priority']) {
                                    case 'high': echo 'border-red-500 bg-red-50'; break;
                                    case 'medium': echo 'border-yellow-500 bg-yellow-50'; break;
                                    case 'low': echo 'border-blue-500 bg-blue-50'; break;
                                    default: echo 'border-gray-500 bg-gray-50'; break;
                                }
                            ?> rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3 mb-2">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                <?php echo htmlspecialchars($announcement['title']); ?>
                                            </h3>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full <?php 
                                                switch($announcement['priority']) {
                                                    case 'high': echo 'bg-red-100 text-red-800'; break;
                                                    case 'medium': echo 'bg-yellow-100 text-yellow-800'; break;
                                                    case 'low': echo 'bg-blue-100 text-blue-800'; break;
                                                    default: echo 'bg-gray-100 text-gray-800'; break;
                                                }
                                            ?>">
                                                <?php echo ucfirst($announcement['priority']); ?> Priority
                                            </span>
                                        </div>
                                        <p class="text-gray-700 mb-3">
                                            <?php echo htmlspecialchars($announcement['content']); ?>
                                        </p>
                                        <div class="flex items-center text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                            <?php echo date('F j, Y', strtotime($announcement['date'])); ?>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-shrink-0">
                                        <svg class="w-6 h-6 <?php 
                                            switch($announcement['priority']) {
                                                case 'high': echo 'text-red-600'; break;
                                                case 'medium': echo 'text-yellow-600'; break;
                                                case 'low': echo 'text-blue-600'; break;
                                                default: echo 'text-gray-600'; break;
                                            }
                                        ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($announcements)): ?>
                            <div class="text-center py-12 text-gray-500">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No announcements</h3>
                                <p class="text-gray-500">Important updates and notices will appear here.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Confirm Logout</h3>
                    <button onclick="closeLogoutModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-gray-600 mb-6">Are you sure you want to logout? You will need to login again to access the dashboard.</p>
                <div class="flex justify-end space-x-3">
                    <button onclick="closeLogoutModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button onclick="performLogout()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Logout functionality
        function confirmLogout() {
            document.getElementById('logoutModal').classList.remove('hidden');
        }
        
        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
        }
        
        function performLogout() {
            window.location.href = '../auth/logout.php';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('logoutModal');
            if (event.target === modal) {
                closeLogoutModal();
            }
        }
        
        // Profile dropdown functionality
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const button = event.target.closest('button[onclick="toggleProfileDropdown()"]');
            
            if (!button && !dropdown.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Announcement creation functionality
        function toggleCreateForm() {
            const form = document.getElementById('createForm');
            form.classList.toggle('hidden');
            
            if (!form.classList.contains('hidden')) {
                // Scroll to form
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        function cancelCreate() {
            document.getElementById('createForm').classList.add('hidden');
            // Clear form
            document.getElementById('announcementTitle').value = '';
            document.getElementById('announcementPriority').value = '';
            document.getElementById('announcementContent').value = '';
        }

        function createAnnouncement() {
            const title = document.getElementById('announcementTitle').value.trim();
            const priority = document.getElementById('announcementPriority').value;
            const content = document.getElementById('announcementContent').value.trim();

            if (!title || !priority || !content) {
                alert('Please fill in all required fields.');
                return;
            }

            if (confirm('Are you sure you want to publish this announcement?')) {
                // Here you would typically make an AJAX call to save the announcement
                // For now, we'll just show a success message
                alert('Announcement published successfully!');
                
                // Clear form and hide it
                cancelCreate();
                
                // In a real implementation, you would reload the announcements list
                // or add the new announcement to the DOM
                location.reload();
            }
        }
    </script>
</body>
</html>

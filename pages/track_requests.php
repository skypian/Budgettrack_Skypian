<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../classes/FileSubmission.php';

$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

$fileSubmission = new FileSubmission();
$userSubmissions = $fileSubmission->getUserSubmissions($userId, 20);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetTrack - Track Requests</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        maroon: '#800000',
                        'maroon-dark': '#5a0000',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <div id="sidebar" class="fixed left-0 top-0 h-screen bg-white shadow-lg border-r border-gray-200 transition-all duration-300 z-40 overflow-y-auto w-64">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-maroon sidebar-text">BudgetTrack</h2>
                    <p class="text-sm text-gray-600 sidebar-text">Department Portal</p>
                </div>
                <button onclick="toggleSidebar()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg id="sidebarToggleIcon" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                </button>
            </div>
            
            <nav class="mt-6">
                <a href="dept_dashboard.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Dashboard</span>
                </a>
                <a href="submit_documents.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Submit PPMP & LIB</span>
                </a>
                <a href="track_requests.php" class="flex items-center px-6 py-3 text-maroon bg-red-50 border-r-4 border-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Track Requests</span>
                </a>
                <a href="budget_report.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Reports</span>
                </a>
                <a href="notifications.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 102.828 2.828l6.414 6.414a2 2 0 01-2.828 2.828L4.828 7z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Notifications</span>
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col" style="margin-left: 256px;">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-maroon">Track Requests</h1>
                        <p class="text-gray-600">Monitor the status of your submitted files</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button onclick="goBack()" class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i>Back
                        </button>
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
                                    <a href="account_settings.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Settings
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold text-maroon">Your Submissions</h2>
                        <p class="text-gray-600">Track the status of your PPMP and LIB submissions</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Info</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Review Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($userSubmissions)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                            <i class="fas fa-inbox text-4xl mb-4"></i>
                                            <p>No submissions found</p>
                                            <p class="text-sm mt-2">Submit your first PPMP or LIB file to track it here.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($userSubmissions as $submission): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <?php if ($submission['submission_type'] === 'PPMP'): ?>
                                                            <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                                <i class="fas fa-file-upload text-blue-600"></i>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                                                                <i class="fas fa-book text-green-600"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($submission['file_name']); ?></div>
                                                        <div class="text-sm text-gray-500">
                                                            <?php echo $fileSubmission->formatFileSize($submission['file_size']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $submission['submission_type'] === 'PPMP' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                                    <?php echo $submission['submission_type']; ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('M j, Y g:i A', strtotime($submission['submitted_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <?php
                                                $statusColors = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'approved' => 'bg-green-100 text-green-800',
                                                    'rejected' => 'bg-red-100 text-red-800'
                                                ];
                                                $statusColor = $statusColors[$submission['status']] ?? 'bg-gray-100 text-gray-800';
                                                ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                                    <?php echo ucfirst($submission['status']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                <?php echo $submission['review_notes'] ? htmlspecialchars($submission['review_notes']) : '-'; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
      </div>
</div>

    <!-- Logout Confirmation Modal -->
<div id="logoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Logout</h3>
                <p class="text-sm text-gray-500 mb-6">Are you sure you want to logout?</p>
                <div class="flex justify-end space-x-3">
                    <button onclick="closeLogoutModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Cancel
                    </button>
                    <button onclick="logout()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Logout
                    </button>
      </div>
    </div>
  </div>
</div>

<script>
        function goBack() {
            window.location.href = 'dept_dashboard.php';
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

        function confirmLogout() {
            document.getElementById('logoutModal').classList.remove('hidden');
        }

        function closeLogoutModal() {
            document.getElementById('logoutModal').classList.add('hidden');
        }

        function logout() {
            window.location.href = '../auth/logout.php';
        }
        
        // Sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            
            sidebar.classList.toggle('sidebar-collapsed');
            body.classList.toggle('sidebar-collapsed');
            
            // Adjust main content margin
            const mainContent = document.querySelector('.flex-1');
            if (sidebar.classList.contains('sidebar-collapsed')) {
                mainContent.style.marginLeft = '80px';
            } else {
                mainContent.style.marginLeft = '256px';
            }
            
            // Save state to localStorage
            const isCollapsed = sidebar.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }
        
        // Restore sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.querySelector('.flex-1');
            
            if (isCollapsed) {
                sidebar.classList.add('sidebar-collapsed');
                document.body.classList.add('sidebar-collapsed');
                mainContent.style.marginLeft = '80px';
            }
        });
    </script>
    <style>
        /* Sidebar collapse styles */
        #sidebar.sidebar-collapsed {
            width: 80px !important;
        }
        
        #sidebar.sidebar-collapsed .sidebar-text {
            display: none !important;
        }
        
        #sidebar.sidebar-collapsed .sidebar-icon {
            margin-right: 0 !important;
        }
        
        #sidebar.sidebar-collapsed nav a {
            justify-content: center;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        
        #sidebar.sidebar-collapsed #sidebarToggleIcon {
            transform: rotate(180deg);
        }
        
        /* Adjust main content margin when sidebar is collapsed */
        body.sidebar-collapsed .flex-1[style*="margin-left"] {
            margin-left: 80px !important;
        }
    </style>
</body>
</html>

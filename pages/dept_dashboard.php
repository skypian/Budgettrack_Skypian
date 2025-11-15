<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../classes/DepartmentBudget.php';
require_once __DIR__ . '/../classes/FileSubmission.php';
require_once __DIR__ . '/../classes/Notification.php';

$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$userRole = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : 'user';
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$departmentId = isset($_SESSION['department_id']) ? $_SESSION['department_id'] : null;

// Get department budget data
$departmentBudget = new DepartmentBudget();
$budget = $departmentBudget->getDepartmentBudget($departmentId);

// Get file submissions
$fileSubmission = new FileSubmission();
$submissions = $fileSubmission->getUserSubmissions($userId, 5);

// Get notifications
$notification = new Notification();
$notifications = $notification->getUserNotifications($userId, 5);
$unreadCount = $notification->getUnreadCount($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetTrack - Department Dashboard</title>
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
                <a href="dept_dashboard.php" class="flex items-center px-6 py-3 text-maroon bg-red-50 border-r-4 border-maroon">
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
                
                <a href="track_requests.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
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
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-r from-maroon via-red-700 to-red-800 shadow-lg">
                <div class="px-6 py-8">
                    <div class="flex justify-between items-start">
                        <div class="text-white">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="bg-white bg-opacity-20 rounded-xl p-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                    <div>
                                    <h1 class="text-3xl font-bold mb-1">Department Dashboard</h1>
                                    <p class="text-red-100 text-sm">Welcome back, <span class="font-semibold"><?php echo htmlspecialchars($username); ?></span>! Manage your department's budget and submissions.</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white bg-opacity-20 backdrop-blur-sm text-white border border-white border-opacity-30">
                                    <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                                    Department Office
                                </span>
                            </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Notification Bell -->
                        <?php 
                        require_once __DIR__ . '/../classes/Notification.php';
                        $notification = new Notification();
                        $notifications = $notification->getUserNotifications($_SESSION['user_id'], 5);
                        $unreadCount = $notification->getUnreadCount($_SESSION['user_id']);
                        include __DIR__ . '/../components/notification_bell.php'; 
                        ?>
                        
                        <div class="relative">
                                <button onclick="toggleProfileDropdown()" class="flex items-center space-x-3 bg-white bg-opacity-20 backdrop-blur-sm px-4 py-2 rounded-xl hover:bg-opacity-30 transition-colors border border-white border-opacity-30">
                                    <div class="w-10 h-10 bg-white bg-opacity-30 rounded-full flex items-center justify-center text-white font-semibold border-2 border-white border-opacity-50">
                                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                                </div>
                                    <div class="text-white">
                                        <div class="font-medium"><?php echo htmlspecialchars($username); ?></div>
                                        <div class="text-xs text-red-100"><?php echo htmlspecialchars($userEmail); ?></div>
                                </div>
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Profile Dropdown -->
                                <div id="profileDropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-2xl z-50 hidden border border-gray-100">
                                    <div class="py-2">
                                        <a href="profile.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Profile
                                    </a>
                                        <a href="change_password.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Change Password
                                    </a>
                                        <div class="border-t border-gray-100 my-1"></div>
                                        <button onclick="confirmLogout()" class="flex items-center w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            </div>
            
            <!-- Content Area -->
            <div class="flex-1 p-6">
                <!-- Budget Overview Section -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 p-8 mb-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-gradient-to-br from-maroon to-red-700 rounded-xl p-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-900">Budget Overview</h2>
                    </div>
                    
                    <!-- Budget Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-2xl p-6 text-center border border-green-200 hover:shadow-lg transition-all duration-300">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-700 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Allocated</p>
                            <p class="text-3xl font-bold text-green-700"><?php echo $budget ? $departmentBudget->formatCurrency($budget['total_allocated']) : '₱0'; ?></p>
                        </div>
                        <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-2xl p-6 text-center border border-red-200 hover:shadow-lg transition-all duration-300">
                            <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-700 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Used</p>
                            <p class="text-3xl font-bold text-red-700"><?php echo $budget ? $departmentBudget->formatCurrency($budget['total_utilized']) : '₱0'; ?></p>
                        </div>
                        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-2xl p-6 text-center border border-yellow-200 hover:shadow-lg transition-all duration-300">
                            <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-yellow-700 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                        </div>
                            <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-2">Remaining</p>
                            <p class="text-3xl font-bold text-yellow-700"><?php echo $budget ? $departmentBudget->formatCurrency($budget['total_remaining']) : '₱0'; ?></p>
                        </div>
                    </div>
                    
                    <!-- Budget Visualization -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Budget Breakdown</h3>
                        
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="font-medium text-gray-700">Allocated</span>
                                <span class="font-medium text-gray-700"><?php echo $budget ? $departmentBudget->formatCurrency($budget['total_allocated']) : '₱0'; ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div class="bg-green-500 h-4 rounded-full" style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="font-medium text-gray-700">Used</span>
                                <span class="font-medium text-gray-700">
                                    <?php 
                                    if ($budget && $budget['total_allocated'] > 0) {
                                        $percentage = ($budget['total_utilized'] / $budget['total_allocated']) * 100;
                                        echo $departmentBudget->formatCurrency($budget['total_utilized']) . ' (' . number_format($percentage, 1) . '%)';
                                    } else {
                                        echo '₱0 (0%)';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <?php 
                                if ($budget && $budget['total_allocated'] > 0) {
                                    $percentage = ($budget['total_utilized'] / $budget['total_allocated']) * 100;
                                    echo '<div class="bg-red-500 h-4 rounded-full" style="width: ' . min($percentage, 100) . '%"></div>';
                                } else {
                                    echo '<div class="bg-red-500 h-4 rounded-full" style="width: 0%"></div>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between text-sm mb-2">
                                <span class="font-medium text-gray-700">Remaining</span>
                                <span class="font-medium text-gray-700">
                                    <?php 
                                    if ($budget && $budget['total_allocated'] > 0) {
                                        $percentage = ($budget['total_remaining'] / $budget['total_allocated']) * 100;
                                        echo $departmentBudget->formatCurrency($budget['total_remaining']) . ' (' . number_format($percentage, 1) . '%)';
                                    } else {
                                        echo '₱0 (0%)';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <?php 
                                if ($budget && $budget['total_allocated'] > 0) {
                                    $percentage = ($budget['total_remaining'] / $budget['total_allocated']) * 100;
                                    echo '<div class="bg-yellow-500 h-4 rounded-full" style="width: ' . max($percentage, 0) . '%"></div>';
                                } else {
                                    echo '<div class="bg-yellow-500 h-4 rounded-full" style="width: 0%"></div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                    <h2 class="text-xl font-bold text-maroon mb-6">Quick Actions</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <button onclick="window.location.href='submit_documents.php'" class="flex items-center justify-center px-6 py-4 bg-maroon text-white rounded-lg hover:bg-maroon-dark transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Submit PPMP & LIB
                        </button>
                        <button onclick="window.location.href='track_requests.php'" class="flex items-center justify-center px-6 py-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                            </svg>
                            Track Requests
                        </button>
                        <button onclick="window.location.href='budget_report.php'" class="flex items-center justify-center px-6 py-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Generate Budget Report
                        </button>
                    </div>
                </div>
                
                <!-- Notifications and Recent Requests -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Notifications/Alerts Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-maroon mb-4">Recent Notifications</h2>
                        <div class="space-y-4">
                            <?php if (empty($notifications)): ?>
                                <div class="text-center py-8 text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.5 19.5a15 15 0 01-1.44-2.1A5.5 5.5 0 014.5 19.5zM19.5 4.5a15 15 0 00-1.44 2.1A5.5 5.5 0 0119.5 4.5z"></path>
                                    </svg>
                                    <p>No notifications yet</p>
                                    <p class="text-sm mt-1">You'll see important updates here</p>
                                </div>
                            <?php else: ?>
                                <?php foreach (array_slice($notifications, 0, 3) as $notification): ?>
                                    <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg border-l-4 <?php 
                                        switch($notification['type']) {
                                            case 'success': echo 'border-green-400 bg-green-50'; break;
                                            case 'error': echo 'border-red-400 bg-red-50'; break;
                                            case 'warning': echo 'border-yellow-400 bg-yellow-50'; break;
                                            default: echo 'border-blue-400 bg-blue-50'; break;
                                        }
                                    ?>">
                                        <svg class="w-5 h-5 mt-0.5 <?php 
                                            switch($notification['type']) {
                                                case 'success': echo 'text-green-600'; break;
                                                case 'error': echo 'text-red-600'; break;
                                                case 'warning': echo 'text-yellow-600'; break;
                                                default: echo 'text-blue-600'; break;
                                            }
                                        ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($notification['title']); ?></p>
                                            <p class="text-sm text-gray-600"><?php 
                                                $nts = isset($notification['created_at']) ? strtotime($notification['created_at']) : false;
                                                echo $nts ? date('M j, Y', $nts) : '—';
                                            ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Recent Submissions Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-xl font-bold text-maroon mb-4">Recent Submissions</h2>
                        <div class="space-y-4">
                            <?php if (empty($submissions)): ?>
                                <div class="text-center py-8 text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p>No submissions yet</p>
                                    <p class="text-sm mt-1">Your PPMP and LIB submissions will appear here</p>
                                </div>
                            <?php else: ?>
                                <?php foreach (array_slice($submissions, 0, 3) as $submission): ?>
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($submission['submission_type']); ?></p>
                                            <p class="text-sm text-gray-600"><?php 
                                                $ts = isset($submission['submitted_at']) ? strtotime($submission['submitted_at']) : (isset($submission['created_at']) ? strtotime($submission['created_at']) : false);
                                                echo $ts ? date('M j, Y', $ts) : '—';
                                            ?></p>
                                        </div>
                                        <span class="px-3 py-1 text-sm font-medium rounded-full <?php 
                                            switch($submission['status']) {
                                                case 'approved': echo 'bg-green-100 text-green-800'; break;
                                                case 'rejected': echo 'bg-red-100 text-red-800'; break;
                                                case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                default: echo 'bg-blue-100 text-blue-800'; break;
                                            }
                                        ?>">
                                            <?php echo ucfirst($submission['status']); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
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
    
    <script>
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
        
        // Coming Soon functionality
        function showComingSoon(feature) {
            alert(feature + ' functionality will be available soon!');
        }

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

        // Add click handlers for quick actions
        document.addEventListener('DOMContentLoaded', function() {
            const quickActions = document.querySelectorAll('button');
        });
    </script>
</body>
</html>
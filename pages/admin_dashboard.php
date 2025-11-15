<?php
session_start();

// Check if user is logged in and has budget access (system administrator)
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'budget') {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/BudgetAllocation.php';
require_once __DIR__ . '/../classes/Notification.php';
require_once __DIR__ . '/../classes/UserActivity.php';

$user = new User();
$budgetAllocation = new BudgetAllocation();
$notification = new Notification();
$userActivity = new UserActivity();

// Get real data
$budgetSummary = $budgetAllocation->getBudgetSummary();
$recentActivities = $userActivity->getRecentActivities(10);
$notifications = $notification->getUserNotifications($_SESSION['user_id'], 5);
$unreadCount = $notification->getUnreadCount($_SESSION['user_id']);

// Get active offices (users with assigned departments)
$activeOffices = $user->getUsersWithDepartments();

// Calculate totals
$totalAllocated = array_sum(array_column($budgetSummary, 'total_allocated'));
$totalUtilized = array_sum(array_column($budgetSummary, 'total_utilized'));
$totalRemaining = array_sum(array_column($budgetSummary, 'total_remaining'));

// Check if user has permission to view admin dashboard
if (!$user->hasPermission($_SESSION['user_id'], 'view_admin_dashboard')) {
    header('Location: ../pages/dashboard.php');
    exit;
}

$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Administrator';
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$userRole = $_SESSION['user_role'];

// User is Budget/Finance Office (system administrator)
$isBudgetOffice = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetTrack - Budget/Finance Office Dashboard</title>
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
                    <p class="text-sm text-gray-600 sidebar-text">Administration Panel</p>
                </div>
                <button onclick="toggleSidebar()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg id="sidebarToggleIcon" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                </button>
            </div>
            
            <nav class="mt-6">
                <a href="admin_dashboard.php" class="flex items-center px-6 py-3 text-maroon bg-red-50 border-r-4 border-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Dashboard</span>
                </a>
                <a href="allocations.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Allocations</span>
                </a>
                <a href="ppmp_lib.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">PPMP & LIB</span>
                </a>
                <a href="reports_admin.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Reports</span>
                </a>
                <a href="user_management.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">User Management</span>
                </a>
                <a href="role_management.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Role Management</span>
                </a>
                <a href="department_management.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Department/Offices</span>
                </a>
                <a href="submissions_admin.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                    </svg>
                    <span class="sidebar-text ml-3">File Submissions</span>
                    <?php 
                    require_once __DIR__ . '/../classes/FileSubmission.php';
                    $fileSubmission = new FileSubmission();
                    $pendingCount = $fileSubmission->getPendingCount();
                    if ($pendingCount > 0): 
                    ?>
                        <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-2 py-1 sidebar-text"><?php echo $pendingCount; ?></span>
                    <?php endif; ?>
                </a>
                <a href="notifications_admin.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                    <div>
                                    <h1 class="text-3xl font-bold mb-1">
                            <?php if ($isBudgetOffice): ?>
                                Budget/Accounting Control Dashboard
                            <?php else: ?>
                                Admin Dashboard
                            <?php endif; ?>
                        </h1>
                                    <p class="text-red-100 text-sm">
                                        Welcome back, <span class="font-semibold"><?php echo htmlspecialchars($username); ?></span>! 
                            <?php if ($isBudgetOffice): ?>
                                You have full control over the budget system and all user permissions.
                            <?php else: ?>
                                Monitor and manage the budget system.
                            <?php endif; ?>
                        </p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white bg-opacity-20 backdrop-blur-sm text-white border border-white border-opacity-30">
                                    <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                                <?php echo $isBudgetOffice ? 'Budget/Accounting Office - Full Control' : ucfirst($userRole); ?>
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <!-- Notification Bell -->
                        <?php include __DIR__ . '/../components/notification_bell.php'; ?>
                        
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
                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-white to-red-50 rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Allocations</p>
                                <p class="text-3xl font-bold text-maroon mb-1"><?php echo $budgetAllocation->formatCurrency($totalAllocated); ?></p>
                                <p class="text-xs text-gray-400">Budget allocated across all departments</p>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-red-500 to-red-700 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-white to-green-50 rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Utilization</p>
                                <p class="text-3xl font-bold text-green-600 mb-1"><?php echo $budgetAllocation->formatCurrency($totalUtilized); ?></p>
                                <p class="text-xs text-gray-400">Funds utilized across departments</p>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-700 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-white to-yellow-50 rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Pending Requests</p>
                                <p class="text-3xl font-bold text-yellow-600 mb-1"><?php echo $unreadCount; ?></p>
                                <p class="text-xs text-gray-400">Notifications awaiting review</p>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-yellow-500 to-yellow-700 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl shadow-lg border border-gray-100 p-6 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Active Offices</p>
                                <p class="text-3xl font-bold text-blue-600 mb-1"><?php echo count($activeOffices); ?></p>
                                <p class="text-xs text-gray-400">Departments with active users</p>
                            </div>
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Recent User Activity</h3>
                        <span class="bg-blue-100 text-blue-800 text-sm font-medium px-2.5 py-0.5 rounded-full">
                            <?php echo count($recentActivities); ?> activities
                        </span>
                </div>
                
                        <div class="space-y-4">
                            <?php if (empty($recentActivities)): ?>
                            <div class="text-center py-8 text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                                <p>No recent activities found</p>
                            </div>
                            <?php else: ?>
                                <?php foreach ($recentActivities as $activity): ?>
                                <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex-shrink-0">
                                        <?php echo $userActivity->getActivityIcon($activity['activity_type']); ?>
                                </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-gray-900">
                                                <span class="font-semibold"><?php echo htmlspecialchars($activity['first_name'] . ' ' . $activity['last_name']); ?></span>
                                                <?php echo $userActivity->formatActivityType($activity['activity_type']); ?>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                                            </p>
                                        </div>
                                        <div class="mt-1 flex items-center space-x-2 text-xs text-gray-500">
                                            <span><?php echo htmlspecialchars($activity['dept_name'] ?? 'No Department'); ?></span>
                                            <span>•</span>
                                            <span><?php echo htmlspecialchars($activity['role_name'] ?? 'No Role'); ?></span>
                                            <?php if ($activity['ip_address']): ?>
                                                <span>•</span>
                                                <span>IP: <?php echo htmlspecialchars($activity['ip_address']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                </div>
                            </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                <!-- Active Users Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Active Department Users</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($activeOffices)): ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No active department users found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($activeOffices as $officeUser): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-10 h-10 bg-maroon rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                                        <?php echo strtoupper(substr($officeUser['first_name'], 0, 1) . substr($officeUser['last_name'], 0, 1)); ?>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?php echo htmlspecialchars($officeUser['first_name'] . ' ' . $officeUser['last_name']); ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($officeUser['dept_name'] ?? 'No Department'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($officeUser['role_name'] ?? 'No Role'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo htmlspecialchars($officeUser['email'] ?? 'No Email'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Active
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- System Notifications -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">System Notifications</h3>
                        <div class="space-y-4">
                            <?php if (empty($notifications)): ?>
                                <p class="text-gray-500 text-center py-4">No notifications</p>
                            <?php else: ?>
                                <?php foreach ($notifications as $notif): ?>
                                    <?php
                                    $bgColor = '';
                                    $borderColor = '';
                                    $textColor = '';
                                    $iconColor = '';
                                    
                                    switch ($notif['type']) {
                                        case 'success':
                                            $bgColor = 'bg-green-50';
                                            $borderColor = 'border-green-400';
                                            $textColor = 'text-green-800';
                                            $iconColor = 'text-green-600';
                                            break;
                                        case 'warning':
                                            $bgColor = 'bg-yellow-50';
                                            $borderColor = 'border-yellow-400';
                                            $textColor = 'text-yellow-800';
                                            $iconColor = 'text-yellow-600';
                                            break;
                                        case 'error':
                                            $bgColor = 'bg-red-50';
                                            $borderColor = 'border-red-400';
                                            $textColor = 'text-red-800';
                                            $iconColor = 'text-red-600';
                                            break;
                                        default:
                                            $bgColor = 'bg-blue-50';
                                            $borderColor = 'border-blue-400';
                                            $textColor = 'text-blue-800';
                                            $iconColor = 'text-blue-600';
                                    }
                                    ?>
                                    <div class="flex items-start space-x-3 p-3 <?php echo $bgColor; ?> rounded-lg border-l-4 <?php echo $borderColor; ?>">
                                        <svg class="w-5 h-5 <?php echo $iconColor; ?> mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                            <p class="font-medium <?php echo $textColor; ?>"><?php echo htmlspecialchars($notif['title']); ?></p>
                                            <p class="text-sm <?php echo $iconColor; ?>"><?php echo htmlspecialchars($notif['message']); ?></p>
                                            <p class="text-xs text-gray-500 mt-1"><?php echo date('M j, Y g:i A', strtotime($notif['created_at'])); ?></p>
                                </div>
                            </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                    </div>
                </div>
                
                <!-- Budget/Accounting Office Controls -->
                <?php if ($isBudgetOffice): ?>
                <div class="bg-red-50 border border-red-200 rounded-xl shadow-sm p-6 mb-8">
                    <div class="flex items-center mb-4">
                        <div class="w-8 h-8 bg-red-600 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-red-800">Budget/Accounting Office Controls</h3>
                    </div>
                    <p class="text-red-700 mb-4">As Budget/Accounting Office, you have complete control over the system, including admin permissions.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <a href="user_management.php" class="flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            Manage All Users
                        </a>
                        <a href="role_management.php" class="flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                            Control Admin Permissions
                        </a>
                        <a href="admin_control.php" class="flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            System Override
                        </a>
                    </div>
                </div>
                <?php endif; ?>

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
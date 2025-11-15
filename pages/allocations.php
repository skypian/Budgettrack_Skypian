<?php
session_start();
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['budget', 'school_admin'])) {
    header('Location: ../login.php');
    exit;
}
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Administrator';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • Allocations</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Handsontable and SheetJS for Excel-like grid/import/export -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable@13.0.0/dist/handsontable.full.min.css">
    <script src="https://cdn.jsdelivr.net/npm/handsontable@13.0.0/dist/handsontable.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <style>
      #allocGrid {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      }
      .handsontable {
        font-size: 13px;
      }
      .handsontable th {
        background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
        font-weight: 600;
        color: #212529;
        border: 1px solid #dee2e6;
        text-align: center;
        padding: 8px 4px;
      }
      .handsontable td {
        border: 1px solid #dee2e6;
        padding: 4px;
      }
      .handsontable .ht_clone_top th {
        background: linear-gradient(to bottom, #f8f9fa 0%, #e9ecef 100%);
      }
      .handsontable .currentRow {
        background-color: #e3f2fd !important;
      }
      .handsontable .currentCol {
        background-color: #fff3e0 !important;
      }
      .handsontable .area {
        background-color: #e8f5e9 !important;
      }
      .handsontable .ht_master .wtHolder {
        box-shadow: none;
      }
      .handsontable .ht_master table {
        border-collapse: separate;
        border-spacing: 0;
      }
    </style>
    <script>
        tailwind.config = { theme: { extend: { colors: { maroon: '#800000','maroon-dark':'#5a0000' } } } }
    </script>
</head>
<body class="bg-gray-50">
<div class="flex min-h-screen">
    <aside class="w-64 bg-white border-r">
        <div class="p-6 border-b">
            <h2 class="text-2xl font-bold text-maroon">BudgetTrack</h2>
            <p class="text-sm text-gray-600">Administration Panel</p>
        </div>
        <nav class="mt-6">
            <a href="admin_dashboard.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                </svg>
                Dashboard
            </a>
            <a href="allocations.php" class="flex items-center px-6 py-3 text-maroon bg-red-50 border-r-4 border-maroon">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
                Allocations
            </a>
            <a href="ppmp_lib.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                PPMP & LIB
            </a>
            <a href="reports_admin.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Reports
            </a>
            <a href="user_management.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                User Management
            </a>
            <a href="role_management.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Role Management
            </a>
            <a href="department_management.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Departments
            </a>
            <a href="submissions_admin.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                </svg>
                File Submissions
            </a>
            <a href="notifications_admin.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 102.828 2.828l6.414 6.414a2 2 0 01-2.828 2.828L4.828 7z"></path>
                </svg>
                Notifications
            </a>
        </nav>
    </aside>
    <main class="flex-1">
        <header class="bg-white border-b px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Allocations</h1>
                    <p class="text-gray-600">Set and manage allocations by department and category.</p>
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
                        <button onclick="toggleProfileDropdown()" class="flex items-center space-x-3 bg-gray-50 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="w-10 h-10 bg-maroon rounded-full flex items-center justify-center text-white font-semibold">
                                <?php echo strtoupper(substr($username, 0, 1)); ?>
                            </div>
                            <div>
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($username); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
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
        </header>
        <section class="p-6">
            <!-- Excel-like Sheet Tabs and Grid -->
            <div id="sheetContainer" class="bg-white border rounded-xl overflow-hidden">
                <!-- Sheet Tabs -->
                <div class="bg-gray-100 border-b flex items-center overflow-x-auto" id="sheetTabs">
                    <button id="btnNewSheet" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Sheet
                    </button>
                </div>

                <!-- Formatting Toolbar -->
                <div class="p-3 border-b bg-gray-100">
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- Font Family -->
                        <select id="fontFamily" class="h-8 px-3 border border-gray-300 rounded text-sm bg-white">
                            <option value="Arial">Arial</option>
                            <option value="Aptos Narrow" selected>Aptos Narrow</option>
                            <option value="Calibri">Calibri</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Courier New">Courier New</option>
                            <option value="Verdana">Verdana</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Comic Sans MS">Comic Sans MS</option>
                        </select>
                        
                        <!-- Font Size -->
                        <select id="fontSize" class="h-8 px-3 border border-gray-300 rounded text-sm bg-white">
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                            <option value="11" selected>11</option>
                            <option value="12">12</option>
                            <option value="14">14</option>
                            <option value="16">16</option>
                            <option value="18">18</option>
                            <option value="20">20</option>
                            <option value="24">24</option>
                            <option value="28">28</option>
                            <option value="36">36</option>
                        </select>
                        
                        <div class="h-6 w-px bg-gray-300"></div>
                        
                        <!-- Bold, Italic, Underline -->
                        <button id="btnBold" class="h-8 w-8 border border-gray-300 rounded hover:bg-gray-200 flex items-center justify-center" title="Bold">
                            <span class="font-bold text-sm">B</span>
                        </button>
                        <button id="btnItalic" class="h-8 w-8 border border-gray-300 rounded hover:bg-gray-200 flex items-center justify-center" title="Italic">
                            <span class="italic text-sm">I</span>
                        </button>
                        <button id="btnUnderline" class="h-8 w-8 border border-gray-300 rounded hover:bg-gray-200 flex items-center justify-center" title="Underline">
                            <span class="underline text-sm">U</span>
                        </button>
                        
                        <div class="h-6 w-px bg-gray-300"></div>
                        
                        <!-- Font Size Increase/Decrease -->
                        <button id="btnIncreaseFont" class="h-8 w-8 border border-gray-300 rounded hover:bg-gray-200 flex items-center justify-center" title="Increase Font Size">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                            </svg>
                            <span class="text-xs ml-0.5">A</span>
                        </button>
                        <button id="btnDecreaseFont" class="h-8 w-8 border border-gray-300 rounded hover:bg-gray-200 flex items-center justify-center" title="Decrease Font Size">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            <span class="text-xs ml-0.5">A</span>
                        </button>
                        
                        <div class="h-6 w-px bg-gray-300"></div>
                        
                        <!-- Text Alignment -->
                        <div class="flex flex-col gap-0.5">
                            <div class="flex gap-0.5">
                                <button id="btnAlignLeft" class="h-4 w-4 border border-gray-300 rounded hover:bg-gray-200 flex items-center justify-center" title="Align Left">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 3h18v2H3V3zm0 4h12v2H3V7zm0 4h18v2H3v-2zm0 4h12v2H3v-2zm0 4h18v2H3v-2z"/>
                                    </svg>
                                </button>
                                <button id="btnAlignCenter" class="h-4 w-4 border border-gray-300 rounded hover:bg-gray-200 flex items-center justify-center" title="Align Center">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 3h18v2H3V3zm3 4h12v2H6V7zm-3 4h18v2H3v-2zm3 4h12v2H6v-2zm-3 4h18v2H3v-2z"/>
                                    </svg>
                                </button>
                                <button id="btnAlignRight" class="h-4 w-4 border border-gray-300 rounded hover:bg-gray-200 flex items-center justify-center" title="Align Right">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 3h18v2H3V3zm6 4h12v2H9V7zm-6 4h18v2H3v-2zm6 4h12v2H9v-2zm-6 4h18v2H3v-2z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="flex gap-0.5">
                                <button id="btnAlignJustify" class="h-4 w-4 border border-gray-300 rounded hover:bg-gray-200 flex items-center justify-center" title="Justify">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 3h18v2H3V3zm0 4h18v2H3V7zm0 4h18v2H3v-2zm0 4h18v2H3v-2zm0 4h18v2H3v-2z"/>
                                    </svg>
                                </button>
                                <button id="btnAlignLeft2" class="h-4 w-4 border border-gray-300 rounded hover:bg-gray-200 flex items-center justify-center" title="Align Left">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 3h18v2H3V3zm0 4h12v2H3V7zm0 4h18v2H3v-2zm0 4h12v2H3v-2zm0 4h18v2H3v-2z"/>
                                    </svg>
                                </button>
                                <button id="btnAlignCenter2" class="h-4 w-4 border border-gray-300 rounded hover:bg-gray-200 flex items-center justify-center" title="Align Center">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M3 3h18v2H3V3zm3 4h12v2H6V7zm-3 4h18v2H3v-2zm3 4h12v2H6v-2zm-3 4h18v2H3v-2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="h-6 w-px bg-gray-300"></div>
                        
                        <!-- Font Color -->
                        <div class="relative">
                            <button id="btnFontColor" class="h-8 w-8 border border-gray-300 rounded hover:bg-gray-200 flex items-center justify-center" title="Font Color">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="absolute bottom-0.5 left-0.5 text-xs font-bold" style="color: #ff0000;">A</span>
                            </button>
                            <div id="fontColorPicker" class="absolute top-full left-0 mt-1 bg-gray-800 rounded-lg shadow-xl p-3 z-50 hidden" style="width: 280px;">
                                <div class="mb-2">
                                    <div class="text-white text-xs font-semibold mb-1">Automatic</div>
                                    <div class="flex gap-1">
                                        <button class="w-6 h-6 border-2 border-orange-500 rounded" style="background: #000000;" onclick="setFontColor('#000000')"></button>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <div class="text-white text-xs font-semibold mb-1">Theme Colors</div>
                                    <div id="themeColors" class="grid grid-cols-10 gap-1 mb-1"></div>
                                    <div id="themeColorShades" class="grid grid-cols-10 gap-1"></div>
                                </div>
                                <div class="mb-2">
                                    <div class="text-white text-xs font-semibold mb-1">Standard Colors</div>
                                    <div id="standardColors" class="grid grid-cols-10 gap-1"></div>
                                </div>
                                <div class="pt-2 border-t border-gray-600">
                                    <button onclick="openMoreFontColors()" class="text-white text-xs hover:text-gray-300 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                                        </svg>
                                        More Colors...
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Background/Fill Color -->
                        <div class="relative">
                            <button id="btnFillColor" class="h-8 w-8 border border-gray-300 rounded hover:bg-gray-200 flex items-center justify-center" title="Fill Color">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                                </svg>
                                <span class="absolute bottom-0.5 left-0.5 w-3 h-1 bg-yellow-300"></span>
                            </button>
                            <div id="fillColorPicker" class="absolute top-full left-0 mt-1 bg-gray-800 rounded-lg shadow-xl p-3 z-50 hidden" style="width: 280px;">
                                <div class="mb-2">
                                    <div class="text-white text-xs font-semibold mb-1">Theme Colors</div>
                                    <div id="fillThemeColors" class="grid grid-cols-10 gap-1 mb-1"></div>
                                    <div id="fillThemeColorShades" class="grid grid-cols-10 gap-1"></div>
                                </div>
                                <div class="mb-2">
                                    <div class="text-white text-xs font-semibold mb-1">Standard Colors</div>
                                    <div id="fillStandardColors" class="grid grid-cols-10 gap-1"></div>
                                </div>
                                <div class="pt-2 border-t border-gray-600">
                                    <button onclick="setFillColor('transparent')" class="text-white text-xs hover:text-gray-300 flex items-center gap-1 w-full">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        No Fill
                                    </button>
                                    <button onclick="openMoreFillColors()" class="text-white text-xs hover:text-gray-300 flex items-center gap-1 mt-1">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                                        </svg>
                                        More Colors...
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Toolbar -->
                <div class="p-4 border-b bg-gray-50">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-2 flex-wrap">
                            <button id="btnImport" class="h-10 px-5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm font-medium flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Import
                            </button>
                            <button id="btnExport" class="h-10 px-5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm font-medium flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export
                            </button>
                            <input id="importFile" type="file" accept=".xlsx,.xls,.csv" class="hidden" />
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <button id="btnOpenSheets" class="h-10 px-5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow-sm font-medium flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-4l-2-2H5a2 2 0 00-2 2z"></path>
                                </svg>
                                Open Sheet
                            </button>
                            <button id="btnFullscreen" class="h-10 px-4 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-colors shadow-sm font-medium flex items-center gap-2" title="Toggle Fullscreen">
                                <!-- <> icon (expand) -->
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3H5a2 2 0 00-2 2v3m0 8v3a2 2 0 002 2h3m8-18h3a2 2 0 012 2v3m0 8v3a2 2 0 01-2 2h-3"/>
                                </svg>
                                <span class="hidden sm:inline">Fullscreen</span>
                            </button>
                            <button id="btnAddRow" class="h-10 px-5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors shadow-sm font-medium flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Row
                            </button>
                            <button id="btnDeleteRow" class="h-10 px-5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors shadow-sm font-medium flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Row
                            </button>
                            <button id="btnSaveSheet" class="h-10 px-5 bg-maroon text-white rounded-lg hover:bg-maroon-dark transition-colors shadow-sm font-medium flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Sheet
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Grid Container -->
                <div id="gridWrapper" class="overflow-auto border-2 border-gray-300 bg-white" style="max-height: 70vh;">
                    <div id="allocGrid" class="min-w-full"></div>
                </div>
            </div>
        </section>
    </main>
</div>
<div id="logoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Confirm Logout</h3>
        <button onclick="closeLogoutModal()" class="text-gray-400 hover:text-gray-600">✕</button>
      </div>
      <p class="text-gray-600 mb-6">Are you sure you want to logout?</p>
      <div class="flex justify-end gap-3">
        <button onclick="closeLogoutModal()" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
        <button onclick="performLogout()" class="px-4 py-2 bg-red-600 text-white rounded">Logout</button>
      </div>
    </div>
  </div>
</div>

<!-- Save Sheet Modal -->
<div id="saveSheetModal" class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-md hidden transition-opacity duration-300" style="z-index: 9999;">
  <div class="flex items-center justify-center min-h-screen p-4" style="z-index: 10000;">
    <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-100 relative" style="animation: modalSlideIn 0.3s ease-out; z-index: 10001;">
      <!-- Header with gradient -->
      <div class="bg-gradient-to-r from-maroon to-red-700 rounded-t-2xl px-6 py-5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="bg-white bg-opacity-20 rounded-lg p-2">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-xl font-bold text-white">Save Sheet</h3>
              <p class="text-red-100 text-sm">Store your spreadsheet for future access</p>
            </div>
          </div>
          <button onclick="closeSaveSheetModal()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-lg p-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Form Content -->
      <form id="saveSheetForm" onsubmit="handleSaveSheet(event)" class="p-6">
        <div class="space-y-5">
          <!-- Sheet Name Input -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              Sheet Name
            </label>
            <input type="text" id="sheetNameInput" required 
                   class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:border-maroon focus:ring-2 focus:ring-maroon focus:ring-opacity-20 transition-all outline-none"
                   placeholder="Enter a descriptive name for your sheet">
          </div>
          
          <!-- Department Select -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-maroon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
              Department
            </label>
            <select id="sheetDeptInput" required 
                    class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:border-maroon focus:ring-2 focus:ring-maroon focus:ring-opacity-20 transition-all outline-none bg-white appearance-none cursor-pointer">
              <option value="">Select a department</option>
            </select>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
          <button type="button" onclick="closeSaveSheetModal()" 
                  class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 font-medium transition-all duration-200 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            Cancel
          </button>
          <button type="submit" 
                  class="px-6 py-2.5 bg-gradient-to-r from-maroon to-red-700 text-white rounded-xl hover:from-red-800 hover:to-red-900 font-semibold shadow-lg hover:shadow-xl transition-all duration-200 flex items-center gap-2 transform hover:scale-105">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Save Sheet
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Dim Handsontable when modal is open */
body.modal-open #allocGrid,
body.modal-open .handsontable {
  opacity: 0.3 !important;
  pointer-events: none !important;
  z-index: 1 !important;
}

/* Ensure modal stays on top */
#saveSheetModal:not(.hidden),
#unsavedModal:not(.hidden),
#navigationModal:not(.hidden) {
  z-index: 9999 !important;
}

#saveSheetModal:not(.hidden) > div,
#unsavedModal:not(.hidden) > div,
#navigationModal:not(.hidden) > div {
  z-index: 10000 !important;
}

#saveSheetModal:not(.hidden) > div > div,
#unsavedModal:not(.hidden) > div > div,
#navigationModal:not(.hidden) > div > div {
  z-index: 10001 !important;
}
</style>

<!-- Unsaved Changes Modal -->
<div id="unsavedModal" class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-md hidden transition-opacity duration-300" style="z-index: 9999;">
  <div class="flex items-center justify-center min-h-screen p-4" style="z-index: 10000;">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 relative" style="z-index: 10001;">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Unsaved Changes</h3>
      </div>
      <p class="text-gray-600 mb-6">You have unsaved changes. Do you want to save before switching sheets?</p>
      <div class="flex justify-end gap-3">
        <button onclick="handleUnsavedCancel()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
        <button onclick="handleUnsavedDiscard()" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">Discard</button>
        <button onclick="handleUnsavedSave()" class="px-4 py-2 bg-maroon text-white rounded hover:bg-maroon-dark">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Navigation Unsaved Changes Modal -->
<div id="navigationModal" class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-md hidden transition-opacity duration-300" style="z-index: 9999;">
  <div class="flex items-center justify-center min-h-screen p-4" style="z-index: 10000;">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 relative" style="z-index: 10001;">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Unsaved Changes</h3>
        <button onclick="handleNavigationCancel()" class="text-gray-400 hover:text-gray-600">✕</button>
      </div>
      <p class="text-gray-600 mb-6">You have unsaved changes in your sheet(s). Do you want to save before leaving this page?</p>
      <div class="flex justify-end gap-3">
        <button onclick="handleNavigationCancel()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
        <button onclick="handleNavigationDiscard()" class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">Leave Without Saving</button>
        <button onclick="handleNavigationSave()" class="px-4 py-2 bg-maroon text-white rounded hover:bg-maroon-dark">Save & Leave</button>
      </div>
    </div>
  </div>
</div>

<!-- File Explorer Modal -->
<div id="fileExplorerModal" class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-md hidden transition-opacity duration-300" style="z-index: 9999;">
  <div class="flex items-center justify-center min-h-screen p-4" style="z-index: 10000;">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full transform transition-all duration-300 scale-100 relative" style="animation: modalSlideIn 0.3s ease-out; z-index: 10001; max-height: 90vh; display: flex; flex-direction: column;">
      <!-- Header with gradient -->
      <div class="bg-gradient-to-r from-maroon to-red-700 rounded-t-2xl px-6 py-5 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="bg-white bg-opacity-20 rounded-xl p-2">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-4l-2-2H5a2 2 0 00-2 2z"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-xl font-bold text-white">Open Saved Sheet</h3>
            <p class="text-red-100 text-sm">Browse and open your saved allocation sheets</p>
          </div>
        </div>
        <button onclick="closeFileExplorerModal()" class="text-white hover:text-red-200 transition-colors p-2 rounded-lg hover:bg-white hover:bg-opacity-20">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      
      <!-- Search and Filter Bar -->
      <div class="px-6 py-4 border-b bg-gray-50">
        <div class="flex items-center gap-3 flex-wrap">
          <div class="flex-1 min-w-[250px]">
            <div class="relative">
              <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <input type="text" id="modalSheetSearch" placeholder="Search sheets by name..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon focus:border-transparent">
            </div>
          </div>
          <div class="flex items-center gap-3">
            <select id="modalSheetDeptFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon focus:border-transparent">
              <option value="">All Departments</option>
            </select>
            <select id="modalSheetYearFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon focus:border-transparent">
              <option value="">All Years</option>
            </select>
          </div>
        </div>
      </div>
      
      <!-- Sheets List -->
      <div class="flex-1 overflow-y-auto px-6 py-4">
        <div id="modalSavedSheetsList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Sheets will be loaded here -->
        </div>
      </div>
      
      <!-- Footer -->
      <div class="px-6 py-4 border-t bg-gray-50 rounded-b-2xl">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-600" id="modalSheetCount">0 sheets found</p>
          <button onclick="closeFileExplorerModal()" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors font-medium">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add Allocation Modal -->
<div id="addAllocationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
  <div class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Add New Allocation</h3>
        <button onclick="closeAddAllocationModal()" class="text-gray-400 hover:text-gray-600">✕</button>
      </div>
      <form id="addAllocationForm">
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
            <select id="newDeptId" class="w-full border rounded px-3 py-2" required>
              <option value="">Select Department</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select id="newCategoryId" class="w-full border rounded px-3 py-2" required>
              <option value="">Select Category</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Fiscal Year</label>
            <input id="newFiscalYear" type="number" class="w-full border rounded px-3 py-2" min="2000" max="2099" required />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Allocated Amount</label>
            <input id="newAllocatedAmount" type="number" step="0.01" min="0" class="w-full border rounded px-3 py-2" placeholder="0.00" required />
          </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
          <button type="button" onclick="closeAddAllocationModal()" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
          <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Add Allocation</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function confirmLogout(){document.getElementById('logoutModal').classList.remove('hidden')} 
function closeLogoutModal(){document.getElementById('logoutModal').classList.add('hidden')} 
function performLogout(){window.location.href='../auth/logout.php'}

// Add Allocation Modal functions
function openAddAllocationModal(){
  document.getElementById('addAllocationModal').classList.remove('hidden');
  // Set current year as default
  document.getElementById('newFiscalYear').value = new Date().getFullYear();
}
function closeAddAllocationModal(){
  document.getElementById('addAllocationModal').classList.add('hidden');
  document.getElementById('addAllocationForm').reset();
}

// Back button functionality
function goBack() {
  // Check user role and redirect to appropriate dashboard
  <?php if ($_SESSION['user_role'] === 'budget'): ?>
    window.location.href = 'admin_dashboard.php';
  <?php elseif ($_SESSION['user_role'] === 'school_admin'): ?>
    window.location.href = 'school_admin_dashboard.php';
  <?php else: ?>
    window.location.href = 'dept_dashboard.php';
  <?php endif; ?>
}

// Fullscreen handling for grid + toolbars
function toggleFullscreen() {
  const container = document.getElementById('sheetContainer');
  if (!container) return;
  if (!document.fullscreenElement) {
    if (container.requestFullscreen) container.requestFullscreen();
  } else {
    if (document.exitFullscreen) document.exitFullscreen();
  }
}

document.addEventListener('fullscreenchange', () => {
  const container = document.getElementById('sheetContainer');
  const gridWrapper = document.getElementById('gridWrapper');
  if (!gridWrapper) return;
  // When our container is in fullscreen, expand grid area to viewport height minus tabs/toolbars
  if (document.fullscreenElement === container) {
    gridWrapper.style.maxHeight = 'calc(100vh - 220px)';
  } else {
    gridWrapper.style.maxHeight = '70vh';
  }
});

// Suppress browser extension errors
window.addEventListener('error', function(e) {
  // Suppress errors from browser extensions (WIREFRAMEIT, translate-page, etc.)
  if (e.message && (
    e.message.includes('translate-page') ||
    e.message.includes('WIREFRAMEIT') ||
    e.message.includes('play()') ||
    e.message.includes('pause()') ||
    e.filename && e.filename.includes('content-all.js') ||
    e.filename && e.filename.includes('index-C80qlVm9.js')
  )) {
    e.preventDefault();
    return true;
  }
});

// Suppress unhandled promise rejections from extensions
window.addEventListener('unhandledrejection', function(e) {
  if (e.reason && (
    e.reason.message && (
      e.reason.message.includes('translate-page') ||
      e.reason.message.includes('play()') ||
      e.reason.message.includes('pause()')
    ) ||
    e.reason.stack && e.reason.stack.includes('content-all.js')
  )) {
    e.preventDefault();
    return true;
  }
});

// Allocations page logic
document.addEventListener('DOMContentLoaded', () => {
  initAllocationsPage();
});

let departments = [];
let categories = [];
let handsontable;
let originalRowsById = new Map();
let dynamicColumns = []; // Store dynamic column structure
let columnHeaders = []; // Store column headers from Excel

// Sheet management
let sheets = []; // Array of sheet objects: {id, name, data, headers, columns, saved, unsaved}
let currentSheetIndex = 0;
let pendingSheetSwitch = null; // Store pending sheet switch when unsaved changes exist

async function initAllocationsPage(){
  try{
    await loadInitData();
    await loadSavedSheets();
    
    // Initialize with default empty sheet
    createNewSheet();
    
    // Event listeners
    document.getElementById('btnNewSheet').addEventListener('click', () => createNewSheet());
    document.getElementById('btnSaveSheet').addEventListener('click', () => openSaveSheetModal());
    document.getElementById('btnAddRow').addEventListener('click', addEmptyRow);
    document.getElementById('btnDeleteRow').addEventListener('click', deleteSelectedRows);
    document.getElementById('btnExport').addEventListener('click', exportToExcel);
    const importBtn = document.getElementById('btnImport');
    const importFile = document.getElementById('importFile');
    importBtn.addEventListener('click', ()=> importFile.click());
    importFile.addEventListener('change', importFromExcel);
    
    // Open Sheets button
    document.getElementById('btnOpenSheets').addEventListener('click', () => openFileExplorerModal());
      // Fullscreen button
      const fsBtn = document.getElementById('btnFullscreen');
      if (fsBtn) {
        fsBtn.addEventListener('click', toggleFullscreen);
      }
    
    // Modal search and filter event listeners (set once)
    const searchInput = document.getElementById('modalSheetSearch');
    const deptFilter = document.getElementById('modalSheetDeptFilter');
    const yearFilter = document.getElementById('modalSheetYearFilter');
    
    if (searchInput) {
      searchInput.addEventListener('input', loadSavedSheets);
    }
    if (deptFilter) {
      deptFilter.addEventListener('change', loadSavedSheets);
    }
    if (yearFilter) {
      yearFilter.addEventListener('change', loadSavedSheets);
    }
    
    // Track changes for unsaved detection
    if (handsontable) {
      handsontable.addHook('afterChange', markSheetUnsaved);
    }
    
    // Add beforeunload event to warn about unsaved changes
    window.addEventListener('beforeunload', handleBeforeUnload);
    
    // Intercept navigation links
    interceptNavigationLinks();
    
    // Initialize formatting toolbar
    initFormattingToolbar();
  }catch(e){
    console.error('Initialization error:', e);
    showError('Failed to initialize allocations page');
  }
}

// Cell formatting storage
let cellFormats = {}; // Store format: {row_col: {font, size, bold, italic, underline, align, color, fill}}

function initFormattingToolbar() {
  // Initialize color pickers
  initColorPickers();
  
  // Font family and size
  document.getElementById('fontFamily').addEventListener('change', applyFormatting);
  document.getElementById('fontSize').addEventListener('change', applyFormatting);
  
  // Bold, Italic, Underline
  document.getElementById('btnBold').addEventListener('click', () => toggleFormat('bold'));
  document.getElementById('btnItalic').addEventListener('click', () => toggleFormat('italic'));
  document.getElementById('btnUnderline').addEventListener('click', () => toggleFormat('underline'));
  
  // Font size increase/decrease
  document.getElementById('btnIncreaseFont').addEventListener('click', () => changeFontSize(1));
  document.getElementById('btnDecreaseFont').addEventListener('click', () => changeFontSize(-1));
  
  // Alignment
  document.getElementById('btnAlignLeft').addEventListener('click', () => setAlignment('left'));
  document.getElementById('btnAlignLeft2').addEventListener('click', () => setAlignment('left'));
  document.getElementById('btnAlignCenter').addEventListener('click', () => setAlignment('center'));
  document.getElementById('btnAlignCenter2').addEventListener('click', () => setAlignment('center'));
  document.getElementById('btnAlignRight').addEventListener('click', () => setAlignment('right'));
  document.getElementById('btnAlignJustify').addEventListener('click', () => setAlignment('justify'));
  
  // Color pickers
  document.getElementById('btnFontColor').addEventListener('click', (e) => {
    e.stopPropagation();
    toggleColorPicker('fontColorPicker');
  });
  document.getElementById('btnFillColor').addEventListener('click', (e) => {
    e.stopPropagation();
    toggleColorPicker('fillColorPicker');
  });
  
  // Close color pickers when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('#btnFontColor') && !e.target.closest('#fontColorPicker')) {
      document.getElementById('fontColorPicker').classList.add('hidden');
    }
    if (!e.target.closest('#btnFillColor') && !e.target.closest('#fillColorPicker')) {
      document.getElementById('fillColorPicker').classList.add('hidden');
    }
  });
}

function initColorPickers() {
  // Theme colors (10 base colors)
  const themeColors = [
    '#FFFFFF', '#000000', '#44546A', '#5B9BD5', '#70AD47', '#A5A5A5', '#FFC000', '#4472C4', '#70AD47', '#FFC7CE'
  ];
  
  // Generate shades for each theme color
  const generateShades = (color) => {
    // Simplified shade generation - in production, use a proper color library
    return [
      lightenColor(color, 0.8),
      lightenColor(color, 0.6),
      lightenColor(color, 0.4),
      lightenColor(color, 0.2),
      color
    ];
  };
  
  // Standard colors
  const standardColors = [
    '#FF0000', '#FFC000', '#FFFF00', '#92D050', '#00B050', '#00B0F0', '#0070C0', '#002060', '#7030A0', '#000000'
  ];
  
  // Populate font color picker
  const themeColorsDiv = document.getElementById('themeColors');
  const themeColorShadesDiv = document.getElementById('themeColorShades');
  const standardColorsDiv = document.getElementById('standardColors');
  
  themeColors.forEach((color, idx) => {
    const btn = document.createElement('button');
    btn.className = 'w-6 h-6 rounded hover:scale-110 transition-transform border border-gray-600';
    btn.style.background = color;
    btn.onclick = () => setFontColor(color);
    themeColorsDiv.appendChild(btn);
    
    // Add shades column
    const shadeColumn = document.createElement('div');
    shadeColumn.className = 'flex flex-col gap-1';
    generateShades(color).forEach(shade => {
      const shadeBtn = document.createElement('button');
      shadeBtn.className = 'w-6 h-6 rounded hover:scale-110 transition-transform border border-gray-600';
      shadeBtn.style.background = shade;
      shadeBtn.onclick = () => setFontColor(shade);
      shadeColumn.appendChild(shadeBtn);
    });
    themeColorShadesDiv.appendChild(shadeColumn);
  });
  
  standardColors.forEach(color => {
    const btn = document.createElement('button');
    btn.className = 'w-6 h-6 rounded hover:scale-110 transition-transform border border-gray-600';
    btn.style.background = color;
    btn.onclick = () => setFontColor(color);
    standardColorsDiv.appendChild(btn);
  });
  
  // Populate fill color picker (same colors)
  const fillThemeColorsDiv = document.getElementById('fillThemeColors');
  const fillThemeColorShadesDiv = document.getElementById('fillThemeColorShades');
  const fillStandardColorsDiv = document.getElementById('fillStandardColors');
  
  themeColors.forEach((color, idx) => {
    const btn = document.createElement('button');
    btn.className = 'w-6 h-6 rounded hover:scale-110 transition-transform border border-gray-600';
    btn.style.background = color;
    btn.onclick = () => setFillColor(color);
    fillThemeColorsDiv.appendChild(btn);
    
    const shadeColumn = document.createElement('div');
    shadeColumn.className = 'flex flex-col gap-1';
    generateShades(color).forEach(shade => {
      const shadeBtn = document.createElement('button');
      shadeBtn.className = 'w-6 h-6 rounded hover:scale-110 transition-transform border border-gray-600';
      shadeBtn.style.background = shade;
      shadeBtn.onclick = () => setFillColor(shade);
      shadeColumn.appendChild(shadeBtn);
    });
    fillThemeColorShadesDiv.appendChild(shadeColumn);
  });
  
  standardColors.forEach(color => {
    const btn = document.createElement('button');
    btn.className = 'w-6 h-6 rounded hover:scale-110 transition-transform border border-gray-600';
    btn.style.background = color;
    btn.onclick = () => setFillColor(color);
    fillStandardColorsDiv.appendChild(btn);
  });
}

function lightenColor(color, amount) {
  // Simple color lightening - convert hex to RGB, lighten, convert back
  const hex = color.replace('#', '');
  const r = Math.min(255, parseInt(hex.substr(0, 2), 16) + Math.round(255 * amount));
  const g = Math.min(255, parseInt(hex.substr(2, 2), 16) + Math.round(255 * amount));
  const b = Math.min(255, parseInt(hex.substr(4, 2), 16) + Math.round(255 * amount));
  return '#' + [r, g, b].map(x => x.toString(16).padStart(2, '0')).join('');
}

function toggleColorPicker(pickerId) {
  const picker = document.getElementById(pickerId);
  const otherPicker = pickerId === 'fontColorPicker' ? 'fillColorPicker' : 'fontColorPicker';
  document.getElementById(otherPicker).classList.add('hidden');
  picker.classList.toggle('hidden');
}

function getSelectedCells() {
  if (!handsontable) {
    console.warn('Handsontable not initialized');
    return [];
  }
  
  try {
    // First try to get selection
    const selected = handsontable.getSelected();
    
    if (selected && selected.length >= 4) {
      // We have a selection range
      const cells = [];
      for (let i = 0; i < selected.length; i += 4) {
        const rowStart = selected[i];
        const colStart = selected[i + 1];
        const rowEnd = selected[i + 2];
        const colEnd = selected[i + 3];
        
        for (let row = Math.min(rowStart, rowEnd); row <= Math.max(rowStart, rowEnd); row++) {
          for (let col = Math.min(colStart, colEnd); col <= Math.max(colStart, colEnd); col++) {
            cells.push({ row, col, key: `${row}_${col}` });
          }
        }
      }
      return cells;
    }
    
    // If no selection, try to get the active cell
    const active = handsontable.getSelectedLast();
    if (active && active.length >= 2) {
      return [{ row: active[0], col: active[1], key: `${active[0]}_${active[1]}` }];
    }
    
    // Try to get current cell from Handsontable's internal state
    const current = handsontable.getSelectedLast();
    if (current && Array.isArray(current) && current.length >= 2) {
      return [{ row: current[0], col: current[1], key: `${current[0]}_${current[1]}` }];
    }
    
    return [];
  } catch (e) {
    console.error('Error getting selected cells:', e);
    return [];
  }
}

function applyFormatting() {
  try {
    const cells = getSelectedCells();
    if (cells.length === 0) {
      showError('Please select a cell or range of cells first');
      return;
    }
    
    const fontFamily = document.getElementById('fontFamily')?.value;
    const fontSize = document.getElementById('fontSize')?.value;
    
    if (!fontFamily || !fontSize) return;
    
    cells.forEach(({ key }) => {
      if (!cellFormats[key]) cellFormats[key] = {};
      cellFormats[key].font = fontFamily;
      cellFormats[key].size = fontSize;
    });
    
    if (handsontable) {
      handsontable.render();
      markSheetUnsaved();
    }
  } catch (e) {
    console.error('Error applying formatting:', e);
    showError('Failed to apply formatting');
  }
}

function toggleFormat(format) {
  try {
    const cells = getSelectedCells();
    if (cells.length === 0) {
      showError('Please select a cell or range of cells first');
      return;
    }
    
    // Check current state of first cell
    const firstCell = cells[0];
    const firstKey = firstCell.key;
    const currentState = cellFormats[firstKey]?.[format] || false;
    
    cells.forEach(({ key }) => {
      if (!cellFormats[key]) cellFormats[key] = {};
      cellFormats[key][format] = !currentState;
    });
    
    // Update button state
    const btn = document.getElementById(`btn${format.charAt(0).toUpperCase() + format.slice(1)}`);
    if (btn) {
      btn.classList.toggle('bg-gray-300', !currentState);
    }
    
    if (handsontable) {
      handsontable.render();
      markSheetUnsaved();
    }
  } catch (e) {
    console.error('Error toggling format:', e);
    showError('Failed to toggle formatting');
  }
}

function changeFontSize(delta) {
  try {
    const cells = getSelectedCells();
    if (cells.length === 0) {
      showError('Please select a cell or range of cells first');
      return;
    }
    
    const sizes = [8, 9, 10, 11, 12, 14, 16, 18, 20, 24, 28, 36];
    
    cells.forEach(({ key }) => {
      if (!cellFormats[key]) cellFormats[key] = {};
      const currentSize = parseInt(cellFormats[key].size || 11);
      const currentIndex = sizes.indexOf(currentSize);
      let newIndex = currentIndex + delta;
      if (newIndex < 0) newIndex = 0;
      if (newIndex >= sizes.length) newIndex = sizes.length - 1;
      cellFormats[key].size = sizes[newIndex].toString();
    });
    
    // Update dropdown
    const firstCell = cells[0];
    const firstKey = firstCell.key;
    const newSize = cellFormats[firstKey]?.size || '11';
    const fontSizeSelect = document.getElementById('fontSize');
    if (fontSizeSelect) {
      fontSizeSelect.value = newSize;
    }
    
    if (handsontable) {
      handsontable.render();
      markSheetUnsaved();
    }
  } catch (e) {
    console.error('Error changing font size:', e);
    showError('Failed to change font size');
  }
}

function setAlignment(align) {
  try {
    const cells = getSelectedCells();
    if (cells.length === 0) {
      showError('Please select a cell or range of cells first');
      return;
    }
    
    cells.forEach(({ key }) => {
      if (!cellFormats[key]) cellFormats[key] = {};
      cellFormats[key].align = align;
    });
    
    // Update button states
    ['left', 'center', 'right', 'justify'].forEach(a => {
      document.getElementById(`btnAlign${a.charAt(0).toUpperCase() + a.slice(1)}`)?.classList.remove('bg-gray-300');
      document.getElementById(`btnAlign${a.charAt(0).toUpperCase() + a.slice(1)}2`)?.classList.remove('bg-gray-300');
    });
    document.getElementById(`btnAlign${align.charAt(0).toUpperCase() + align.slice(1)}`)?.classList.add('bg-gray-300');
    if (document.getElementById(`btnAlign${align.charAt(0).toUpperCase() + align.slice(1)}2`)) {
      document.getElementById(`btnAlign${align.charAt(0).toUpperCase() + align.slice(1)}2`).classList.add('bg-gray-300');
    }
    
    if (handsontable) {
      handsontable.render();
      markSheetUnsaved();
    }
  } catch (e) {
    console.error('Error setting alignment:', e);
    showError('Failed to set alignment');
  }
}

function setFontColor(color) {
  try {
    const cells = getSelectedCells();
    if (cells.length === 0) {
      showError('Please select a cell or range of cells first');
      document.getElementById('fontColorPicker').classList.add('hidden');
      return;
    }
    
    cells.forEach(({ key }) => {
      if (!cellFormats[key]) cellFormats[key] = {};
      cellFormats[key].color = color;
    });
    
    document.getElementById('fontColorPicker').classList.add('hidden');
    if (handsontable) {
      handsontable.render();
      markSheetUnsaved();
    }
  } catch (e) {
    console.error('Error setting font color:', e);
    showError('Failed to set font color');
  }
}

function setFillColor(color) {
  try {
    const cells = getSelectedCells();
    if (cells.length === 0) {
      showError('Please select a cell or range of cells first');
      document.getElementById('fillColorPicker').classList.add('hidden');
      return;
    }
    
    cells.forEach(({ key }) => {
      if (!cellFormats[key]) cellFormats[key] = {};
      cellFormats[key].fill = color === 'transparent' ? '' : color;
    });
    
    document.getElementById('fillColorPicker').classList.add('hidden');
    if (handsontable) {
      handsontable.render();
      markSheetUnsaved();
    }
  } catch (e) {
    console.error('Error setting fill color:', e);
    showError('Failed to set fill color');
  }
}

function openMoreFontColors() {
  const color = prompt('Enter hex color (e.g., #FF5733):', '#000000');
  if (color && /^#[0-9A-F]{6}$/i.test(color)) {
    setFontColor(color);
  }
}

function openMoreFillColors() {
  const color = prompt('Enter hex color (e.g., #FF5733):', '#FFFFFF');
  if (color && /^#[0-9A-F]{6}$/i.test(color)) {
    setFillColor(color);
  }
}

// Update formatting toolbar to reflect current cell's formatting
function updateFormattingToolbar() {
  try {
    const cells = getSelectedCells();
    if (cells.length === 0) return;
    
    const firstCell = cells[0];
    const format = cellFormats[firstCell.key];
    
    if (format) {
      // Update font family
      const fontFamilySelect = document.getElementById('fontFamily');
      if (fontFamilySelect && format.font) {
        fontFamilySelect.value = format.font;
      }
      
      // Update font size
      const fontSizeSelect = document.getElementById('fontSize');
      if (fontSizeSelect && format.size) {
        fontSizeSelect.value = format.size;
      }
      
      // Update bold, italic, underline buttons
      const boldBtn = document.getElementById('btnBold');
      if (boldBtn) {
        boldBtn.classList.toggle('bg-gray-300', format.bold);
      }
      const italicBtn = document.getElementById('btnItalic');
      if (italicBtn) {
        italicBtn.classList.toggle('bg-gray-300', format.italic);
      }
      const underlineBtn = document.getElementById('btnUnderline');
      if (underlineBtn) {
        underlineBtn.classList.toggle('bg-gray-300', format.underline);
      }
      
      // Update alignment buttons
      if (format.align) {
        ['left', 'center', 'right', 'justify'].forEach(a => {
          document.getElementById(`btnAlign${a.charAt(0).toUpperCase() + a.slice(1)}`)?.classList.remove('bg-gray-300');
          document.getElementById(`btnAlign${a.charAt(0).toUpperCase() + a.slice(1)}2`)?.classList.remove('bg-gray-300');
        });
        document.getElementById(`btnAlign${format.align.charAt(0).toUpperCase() + format.align.slice(1)}`)?.classList.add('bg-gray-300');
        const alignBtn2 = document.getElementById(`btnAlign${format.align.charAt(0).toUpperCase() + format.align.slice(1)}2`);
        if (alignBtn2) {
          alignBtn2.classList.add('bg-gray-300');
        }
      }
    } else {
      // Reset to defaults if no formatting
      const fontFamilySelect = document.getElementById('fontFamily');
      if (fontFamilySelect) fontFamilySelect.value = 'Aptos Narrow';
      const fontSizeSelect = document.getElementById('fontSize');
      if (fontSizeSelect) fontSizeSelect.value = '11';
      
      document.getElementById('btnBold')?.classList.remove('bg-gray-300');
      document.getElementById('btnItalic')?.classList.remove('bg-gray-300');
      document.getElementById('btnUnderline')?.classList.remove('bg-gray-300');
      
      ['left', 'center', 'right', 'justify'].forEach(a => {
        document.getElementById(`btnAlign${a.charAt(0).toUpperCase() + a.slice(1)}`)?.classList.remove('bg-gray-300');
        document.getElementById(`btnAlign${a.charAt(0).toUpperCase() + a.slice(1)}2`)?.classList.remove('bg-gray-300');
      });
    }
  } catch (e) {
    console.error('Error updating formatting toolbar:', e);
  }
}

async function loadInitData(){
  const res = await fetch('../ajax/allocations_init.php');
  if(!res.ok){
    let msg = 'init failed';
    try { msg = (await res.json()).error || msg; } catch(_) {}
    throw new Error('allocations_init: ' + msg + ' ('+res.status+')');
  }
  const data = await res.json();
  departments = data.departments || [];
  
  // Populate department dropdowns
  const sheetDeptFilter = document.getElementById('sheetDeptFilter');
  const sheetDeptInput = document.getElementById('sheetDeptInput');
  const deptOptions = '<option value="">All Departments</option>' +
    departments.map(d=>`<option value="${d.id}">${d.dept_name}</option>`).join('');
  if(sheetDeptFilter) sheetDeptFilter.innerHTML = deptOptions;
  if(sheetDeptInput) {
    sheetDeptInput.innerHTML = '<option value="">Select Department</option>' +
      departments.map(d=>`<option value="${d.id}">${d.dept_name}</option>`).join('');
  }
  
  // Load budget categories for the modal
  const catRes = await fetch('../ajax/get_budget_categories.php');
  if(catRes.ok){
    const catData = await catRes.json();
    categories = catData || [];
    const catSelect = document.getElementById('newCategoryId');
    if(catSelect){
      catSelect.innerHTML = '<option value="">Select Category</option>' +
        categories.map(c=>`<option value="${c.id}">${c.category_name}</option>`).join('');
    }
  }
  
  // Populate department dropdown in modal
  const modalDeptSelect = document.getElementById('newDeptId');
  if(modalDeptSelect){
    modalDeptSelect.innerHTML = '<option value="">Select Department</option>' +
      departments.map(d=>`<option value="${d.id}">${d.dept_name}</option>`).join('');
  }
}

async function loadAllocations(){
  const deptId = document.getElementById('deptFilter')?.value || '';
  const year = document.getElementById('yearFilter')?.value || new Date().getFullYear();
  const params = new URLSearchParams({ fiscal_year: year });
  if(deptId) params.append('department_id', deptId);
  const res = await fetch('../ajax/get_allocations.php?'+params.toString());
  if(!res.ok){ showError('Failed to load allocations'); return; }
  const rows = await res.json();
  
  // Try to load saved grid structure from metadata
  try {
    const metaRes = await fetch(`../ajax/get_allocations_metadata.php?fiscal_year=${year}`);
    if (metaRes.ok) {
      const meta = await metaRes.json();
      if (meta.headers && meta.columns && meta.headers.length > 0) {
        columnHeaders = meta.headers;
        dynamicColumns = meta.columns;
      }
    }
  } catch (e) {
    console.log('No metadata found');
  }
  
  // If we have dynamic columns from saved metadata, use them
  if (dynamicColumns.length > 0 && columnHeaders.length > 0) {
    // Map database rows to saved structure
    const data = rows.map(r => {
      const row = { _id: r.id || null };
      columnHeaders.forEach((header) => {
        // Try to map common field names
        const headerLower = header.toLowerCase();
        if (headerLower.includes('department') || headerLower.includes('dept')) {
          row[header] = r.dept_name || '';
        } else if (headerLower.includes('category') || headerLower.includes('cat')) {
          row[header] = r.category_name || '';
        } else if (headerLower.includes('fiscal') || headerLower.includes('year')) {
          row[header] = r.fiscal_year || year;
        } else if (headerLower.includes('allocated')) {
          row[header] = Number(r.allocated_amount || 0);
        } else if (headerLower.includes('utilized')) {
          row[header] = Number(r.utilized_amount || 0);
        } else if (headerLower.includes('remaining')) {
          row[header] = Number(r.remaining_amount || 0);
        } else {
          row[header] = '';
        }
      });
      return row;
    });
    originalRowsById.clear();
    data.forEach(d => { if (d._id) originalRowsById.set(String(d._id), { ...d }); });
    renderGrid(data, columnHeaders, dynamicColumns);
  } else if (rows.length > 0) {
    // If no saved structure but we have data, create default structure
    const data = rows.map(r => ({
      _id: r.id || null,
      'Department': r.dept_name || '',
      'Category': r.category_name || '',
      'Fiscal Year': r.fiscal_year || year,
      'Allocated Amount': Number(r.allocated_amount || 0),
      'Utilized Amount': Number(r.utilized_amount || 0),
      'Remaining Amount': Number(r.remaining_amount || 0)
    }));
    originalRowsById.clear();
    data.forEach(d => { if (d._id) originalRowsById.set(String(d._id), { ...d }); });
    columnHeaders = ['Department', 'Category', 'Fiscal Year', 'Allocated Amount', 'Utilized Amount', 'Remaining Amount'];
    renderGrid(data, columnHeaders);
  } else {
    // No data and no saved structure - start with empty grid
    columnHeaders = [];
    dynamicColumns = [];
    renderGrid([], [], []);
  }
}

function renderGrid(data, headers = null, columns = null){
  const container = document.getElementById('allocGrid');
  
  // Ensure data is an array
  if (!Array.isArray(data)) {
    console.warn('renderGrid: data is not an array, converting...', data);
    data = [];
  }
  
  // Use provided headers/columns or generate from data
  if (!headers && data.length > 0) {
    headers = Object.keys(data[0]).filter(k => k !== '_id');
  }
  if (!columns && headers) {
    columns = headers.map(header => {
      // Detect column type
      const sampleValue = data.length > 0 ? data[0][header] : '';
      const isNumeric = !isNaN(sampleValue) && sampleValue !== '' && sampleValue !== null;
      return {
        data: header,
        type: isNumeric ? 'numeric' : 'text',
        width: isNumeric ? 150 : 200,
        className: isNumeric ? 'htRight' : 'htLeft'
      };
    });
  }
  
  columnHeaders = headers || columnHeaders;
  dynamicColumns = columns || dynamicColumns;

  if (handsontable) {
    // Update columns if structure changed
    if (columns && columns.length !== handsontable.countCols()) {
      handsontable.updateSettings({ columns: columns, colHeaders: headers });
    }
    // Ensure data is an array before loading
    const dataToLoad = Array.isArray(data) ? data : [];
    handsontable.loadData(dataToLoad);
    return;
  }

  handsontable = new Handsontable(container, {
    data,
    licenseKey: 'non-commercial-and-evaluation',
    rowHeaders: false,
    colHeaders: headers || false,
    columns: columns || [],
    stretchH: 'all',
    height: 600,
    width: '100%',
    manualColumnResize: true,
    manualRowResize: true,
    manualColumnMove: true,
    contextMenu: ['row_above', 'row_below', 'remove_row', '---------', 'copy', 'cut', 'paste'],
    filters: true,
    dropdownMenu: false,
    columnSorting: true,
    sortIndicator: true,
    outsideClickDeselects: false,
    enterBeginsEditing: true,
    tabNavigation: true,
    autoWrapRow: true,
    autoWrapCol: true,
    allowInsertColumn: true,
    allowRemoveColumn: true,
    cells: function(row, col, prop) {
      try {
        const cellProperties = {};
        const key = `${row}_${col}`;
        const format = cellFormats[key];
        
        if (format) {
          // Create a combined renderer for all formatting
          cellProperties.renderer = function(instance, td, row, col, prop, value, cellProperties) {
            // Call the default text renderer
            Handsontable.renderers.TextRenderer.apply(this, arguments);
            
            // Apply font family
            if (format.font) {
              td.style.fontFamily = format.font;
            }
            
            // Apply font size
            if (format.size) {
              td.style.fontSize = format.size + 'px';
            }
            
            // Apply text styles
            if (format.bold) {
              td.style.fontWeight = 'bold';
            }
            if (format.italic) {
              td.style.fontStyle = 'italic';
            }
            if (format.underline) {
              td.style.textDecoration = 'underline';
            }
            
            // Apply colors
            if (format.color) {
              td.style.color = format.color;
            }
            if (format.fill) {
              td.style.backgroundColor = format.fill;
            }
            
            // Apply alignment
            if (format.align) {
              const alignMap = {
                'left': 'left',
                'center': 'center',
                'right': 'right',
                'justify': 'justify'
              };
              if (alignMap[format.align]) {
                td.style.textAlign = alignMap[format.align];
              }
            }
          };
          
          // Also set className for alignment (Handsontable uses this)
          if (format.align) {
            const alignMap = {
              'left': 'htLeft',
              'center': 'htCenter',
              'right': 'htRight',
              'justify': 'htJustify'
            };
            if (alignMap[format.align]) {
              cellProperties.className = (cellProperties.className || '') + ' ' + alignMap[format.align];
            }
          }
        }
        
        return cellProperties;
      } catch (e) {
        console.error('Error in cells function:', e);
        return {};
      }
    },
    afterSelectionEnd: function(row, col, row2, col2) {
      // Update formatting toolbar when selection changes
      setTimeout(() => updateFormattingToolbar(), 50);
    },
    afterChange: function(changes, source) {
      // Skip during initialization
      if (source === 'loadData' || !this) return;
      
      markSheetUnsaved();
      // Update current sheet data
      if (sheets[currentSheetIndex]) {
        sheets[currentSheetIndex].data = this.getSourceData();
        sheets[currentSheetIndex].headers = columnHeaders;
        sheets[currentSheetIndex].columns = dynamicColumns;
      }
    },
    afterColumnMove: function(movedColumns, targetIndexes, dropIndex, movePossible) {
      // Update column headers and columns arrays when columns are moved
      if (movePossible) {
        const movedHeaders = movedColumns.map(col => columnHeaders[col]);
        const movedCols = movedColumns.map(col => dynamicColumns[col]);
        movedColumns.forEach((col, idx) => {
          columnHeaders.splice(col, 1);
          dynamicColumns.splice(col, 1);
        });
        columnHeaders.splice(dropIndex, 0, ...movedHeaders);
        dynamicColumns.splice(dropIndex, 0, ...movedCols);
      }
    },
    afterCreateCol: function(index, amount) {
      const newHeader = `Column ${columnHeaders.length + 1}`;
      columnHeaders.splice(index, 0, newHeader);
      dynamicColumns.splice(index, 0, { data: newHeader, type: 'text', width: 200 });
      // Update all existing rows with new column
      const currentData = handsontable.getSourceData();
      currentData.forEach(row => {
        if (!row.hasOwnProperty(newHeader)) {
          row[newHeader] = '';
        }
      });
      handsontable.loadData(currentData);
    },
    afterRemoveCol: function(index, amount) {
      const removedHeaders = columnHeaders.splice(index, amount);
      dynamicColumns.splice(index, amount);
      // Remove columns from all rows
      const currentData = handsontable.getSourceData();
      currentData.forEach(row => {
        removedHeaders.forEach(header => {
          delete row[header];
        });
      });
      handsontable.loadData(currentData);
    },
    afterSetCellMeta: function(row, col, key, value) {
      // Allow editing column headers by double-clicking
      if (key === 'colHeader' && value) {
        const oldHeader = columnHeaders[col];
        columnHeaders[col] = value;
        dynamicColumns[col].data = value;
        // Update all rows to use new header name
        const currentData = handsontable.getSourceData();
        currentData.forEach(r => {
          if (r.hasOwnProperty(oldHeader)) {
            r[value] = r[oldHeader];
            delete r[oldHeader];
          }
        });
        handsontable.loadData(currentData);
      }
    },
    className: 'htCenter htMiddle',
    renderAllRows: false,
    viewportRowRenderingOffset: 20,
    viewportColumnRenderingOffset: 10
  });
  
  // Enable column header editing by double-clicking header
  const headerCells = container.querySelectorAll('.ht_clone_top th');
  headerCells.forEach((th, index) => {
    th.style.cursor = 'pointer';
    th.addEventListener('dblclick', function() {
      const currentHeader = columnHeaders[index];
      const newHeader = prompt('Rename column:', currentHeader);
      if (newHeader && newHeader !== currentHeader && newHeader.trim() !== '') {
        const oldHeader = columnHeaders[index];
        columnHeaders[index] = newHeader.trim();
        dynamicColumns[index].data = newHeader.trim();
        const currentData = handsontable.getSourceData();
        currentData.forEach(r => {
          if (r.hasOwnProperty(oldHeader)) {
            r[newHeader.trim()] = r[oldHeader];
            delete r[oldHeader];
          }
        });
        handsontable.updateSettings({ colHeaders: columnHeaders });
        handsontable.loadData(currentData);
      }
    });
  });
  
  // Re-attach header editing after grid updates
  handsontable.addHook('afterLoadData', function() {
    setTimeout(() => {
      const headerCells = container.querySelectorAll('.ht_clone_top th');
      headerCells.forEach((th, index) => {
        if (!th.hasAttribute('data-editable')) {
          th.setAttribute('data-editable', 'true');
          th.style.cursor = 'pointer';
          th.title = 'Double-click to rename column';
          th.addEventListener('dblclick', function() {
            const currentHeader = columnHeaders[index];
            const newHeader = prompt('Rename column:', currentHeader);
            if (newHeader && newHeader !== currentHeader && newHeader.trim() !== '') {
              const oldHeader = columnHeaders[index];
              columnHeaders[index] = newHeader.trim();
              dynamicColumns[index].data = newHeader.trim();
              const currentData = handsontable.getSourceData();
              currentData.forEach(r => {
                if (r.hasOwnProperty(oldHeader)) {
                  r[newHeader.trim()] = r[oldHeader];
                  delete r[oldHeader];
                }
              });
              handsontable.updateSettings({ colHeaders: columnHeaders });
              handsontable.loadData(currentData);
            }
          });
        }
      });
    }, 100);
  });
}

function addEmptyRow(){
  if (!handsontable || columnHeaders.length === 0) return;
  const newRow = { _id: null };
  columnHeaders.forEach(header => {
    newRow[header] = '';
  });
  const currentData = handsontable.getSourceData();
  currentData.push(newRow);
  handsontable.loadData(currentData);
  markSheetUnsaved();
}

async function saveAllocation(id){
  try{
    const input = document.querySelector(`input[data-id="${id}"]`);
    if(!input) return;
    const amount = input.value;
    const form = new FormData();
    form.append('id', id);
    form.append('allocated_amount', amount);
    const res = await fetch('../ajax/save_allocation.php', { method:'POST', body: form });
    const data = await res.json();
    if(data.success){ showSuccess('Allocation saved'); loadAllocations(); }
    else { showError(data.message || 'Save failed'); }
  }catch(e){ console.error(e); showError('Save failed'); }
}

async function importCSV(e){
  const file = e.target.files[0];
  if(!file) return;
  const year = document.getElementById('yearFilter')?.value || new Date().getFullYear();
  const form = new FormData();
  form.append('file', file);
  form.append('fiscal_year', year);
  const btn = document.getElementById('btnUpload');
  showLoading(btn);
  try{
    const res = await fetch('../ajax/import_allocations_csv.php', { method:'POST', body: form });
    const data = await res.json();
    if(data.success){ showSuccess(`Imported: ${data.imported}, Updated: ${data.updated}, Skipped: ${data.skipped}`); loadAllocations(); }
    else{ showError(data.message || 'Import failed'); }
  }catch(err){ console.error(err); showError('Import failed'); }
  finally{ hideLoading(btn); e.target.value=''; }
}

function exportToExcel(){
  if (!handsontable) return;
  const data = handsontable.getSourceData();
  const headers = columnHeaders.length > 0 ? columnHeaders : (data.length > 0 ? Object.keys(data[0]).filter(k => k !== '_id') : []);
  
  // Export exactly what's in the grid
  const json = data.map(r => {
    const row = {};
    headers.forEach(header => {
      row[header] = r[header] !== undefined ? r[header] : '';
    });
    return row;
  });
  
  const ws = XLSX.utils.json_to_sheet(json);
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Allocations');
  XLSX.writeFile(wb, 'allocations_' + new Date().getFullYear() + '.xlsx');
  showSuccess('Excel file exported successfully');
}

async function handleAddAllocation(e){
  e.preventDefault();
  try{
    const formData = new FormData();
    formData.append('department_id', document.getElementById('newDeptId').value);
    formData.append('category_id', document.getElementById('newCategoryId').value);
    formData.append('fiscal_year', document.getElementById('newFiscalYear').value);
    formData.append('allocated_amount', document.getElementById('newAllocatedAmount').value);
    
    const res = await fetch('../ajax/create_allocation.php', { method:'POST', body: formData });
    const data = await res.json();
    if(data.success){ 
      showSuccess('Allocation created successfully'); 
      closeAddAllocationModal();
      loadAllocations(); 
    } else { 
      showError(data.message || 'Failed to create allocation'); 
    }
  }catch(e){ 
    console.error(e); 
    showError('Failed to create allocation'); 
  }
}

function downloadTemplate(){
  const header = 'department_code,category_code,fiscal_year,allocated_amount\n';
  const blob = new Blob([header], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url; a.download = 'allocations_template.csv'; a.click();
  URL.revokeObjectURL(url);
}

function importFromExcel(e){
  const file = e.target.files[0];
  if(!file) return;
  const reader = new FileReader();
  reader.onload = function(evt){
    try {
      const data = new Uint8Array(evt.target.result);
      const wb = XLSX.read(data, { type: 'array' });
      const ws = wb.Sheets[wb.SheetNames[0]];
      const rows = XLSX.utils.sheet_to_json(ws, { defval: '', raw: false });
      
      if (rows.length === 0) {
        showError('Excel file is empty');
        return;
      }
      
      // Get headers from first row keys
      const headers = Object.keys(rows[0]);
      columnHeaders = headers;
      
      // Create dynamic columns based on actual Excel structure
      dynamicColumns = headers.map(header => {
        const sampleValue = rows[0][header];
        const isNumeric = !isNaN(sampleValue) && sampleValue !== '' && sampleValue !== null;
        return {
          data: header,
          type: isNumeric ? 'numeric' : 'text',
          width: isNumeric ? 150 : 200,
          className: isNumeric ? 'htRight' : 'htLeft',
          numericFormat: isNumeric ? { pattern: '0,0.00' } : undefined
        };
      });
      
      // Map rows to match column structure
      const mapped = rows.map((r, idx) => {
        const row = { _id: null };
        headers.forEach(header => {
          const val = r[header];
          row[header] = isNaN(val) || val === '' ? val : Number(val);
        });
        return row;
      });
      
      // Render grid with imported data
      renderGrid(mapped, headers, dynamicColumns);
      
      // Update current sheet with imported data
      if (sheets[currentSheetIndex]) {
        sheets[currentSheetIndex].data = mapped;
        sheets[currentSheetIndex].headers = headers;
        sheets[currentSheetIndex].columns = dynamicColumns;
        markSheetUnsaved();
      }
      
      showSuccess(`Imported ${mapped.length} row(s) with ${headers.length} column(s) successfully`);
    } catch (error) {
      console.error('Import error:', error);
      showError('Failed to import file. Please check the file format.');
    }
  };
  reader.readAsArrayBuffer(file);
  e.target.value = '';
}

function findDepartmentIdByName(name){
  const d = departments.find(x => (x.dept_name||'').toLowerCase() === String(name||'').toLowerCase());
  return d ? d.id : null;
}
function findCategoryIdByName(name){
  const c = (categories||[]).find(x => (x.category_name||'').toLowerCase() === String(name||'').toLowerCase());
  return c ? c.id : null;
}

// Sheet Management Functions
function createNewSheet() {
  if (sheets.length > 0 && sheets[currentSheetIndex]?.unsaved) {
    pendingSheetSwitch = () => {
      // Create default columns
      const defaultHeaders = [];
      const defaultColumns = [];
      for (let i = 0; i < 10; i++) {
        const colName = String.fromCharCode(65 + i); // A, B, C, ...
        defaultHeaders.push(colName);
        defaultColumns.push({ data: colName, type: 'text', width: 120 });
      }
      
      // Create default empty rows (20 rows)
      const defaultData = [];
      for (let row = 0; row < 20; row++) {
        const rowData = { _id: null };
        defaultHeaders.forEach(header => {
          rowData[header] = '';
        });
        defaultData.push(rowData);
      }
      
      const newSheet = {
        id: null,
        name: `Sheet${sheets.length + 1}`,
        data: defaultData,
        headers: defaultHeaders,
        columns: defaultColumns,
        saved: false,
        unsaved: false,
        department_id: null,
        fiscal_year: new Date().getFullYear()
      };
      sheets.push(newSheet);
      currentSheetIndex = sheets.length - 1;
      updateSheetTabs();
      loadSheetIntoGrid(newSheet);
    };
    document.getElementById('unsavedModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
    return;
  }
  
  // Create default columns
  const defaultHeaders = [];
  const defaultColumns = [];
  for (let i = 0; i < 10; i++) {
    const colName = String.fromCharCode(65 + i); // A, B, C, ...
    defaultHeaders.push(colName);
    defaultColumns.push({ data: colName, type: 'text', width: 120 });
  }
  
  // Create default empty rows (20 rows)
  const defaultData = [];
  for (let row = 0; row < 20; row++) {
    const rowData = { _id: null };
    defaultHeaders.forEach(header => {
      rowData[header] = '';
    });
    defaultData.push(rowData);
  }
  
  const newSheet = {
    id: null,
    name: `Sheet${sheets.length + 1}`,
    data: defaultData,
    headers: defaultHeaders,
    columns: defaultColumns,
    cell_formats: {},
    saved: false,
    unsaved: false,
    department_id: null,
    fiscal_year: new Date().getFullYear()
  };
  sheets.push(newSheet);
  currentSheetIndex = sheets.length - 1;
  updateSheetTabs();
  loadSheetIntoGrid(newSheet);
}

function switchSheet(index) {
  if (index === currentSheetIndex) return;
  
  if (sheets[currentSheetIndex]?.unsaved) {
    pendingSheetSwitch = () => switchToSheet(index);
    document.getElementById('unsavedModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
    return;
  }
  
  switchToSheet(index);
}

function switchToSheet(index) {
  // Save current sheet's formatting before switching
  if (sheets[currentSheetIndex]) {
    sheets[currentSheetIndex].cell_formats = { ...cellFormats };
  }
  
  currentSheetIndex = index;
  updateSheetTabs();
  const sheet = sheets[index];
  loadSheetIntoGrid(sheet);
}

function updateSheetTabs() {
  const tabsContainer = document.getElementById('sheetTabs');
  tabsContainer.innerHTML = '<button id="btnNewSheet" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-2 whitespace-nowrap"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>New Sheet</button>';
  
  sheets.forEach((sheet, index) => {
    const tab = document.createElement('button');
    tab.className = `px-4 py-2 border-b-2 whitespace-nowrap flex items-center gap-2 ${index === currentSheetIndex ? 'border-maroon bg-white font-semibold' : 'border-transparent hover:bg-gray-200'}`;
    tab.innerHTML = `
      <span>${sheet.name}${sheet.unsaved ? '*' : ''}</span>
      ${index !== currentSheetIndex ? `<button onclick="event.stopPropagation(); closeSheet(${index})" class="ml-2 text-gray-400 hover:text-red-600">✕</button>` : ''}
    `;
    tab.onclick = () => switchSheet(index);
    tabsContainer.appendChild(tab);
  });
  
  document.getElementById('btnNewSheet').addEventListener('click', () => createNewSheet());
}

function closeSheet(index) {
  if (sheets[index]?.unsaved) {
    if (!confirm('This sheet has unsaved changes. Close anyway?')) return;
  }
  sheets.splice(index, 1);
  if (currentSheetIndex >= sheets.length) currentSheetIndex = sheets.length - 1;
  if (sheets.length === 0) {
    createNewSheet();
  } else {
    updateSheetTabs();
    loadSheetIntoGrid(sheets[currentSheetIndex]);
  }
}

function loadSheetIntoGrid(sheet) {
  columnHeaders = Array.isArray(sheet.headers) ? sheet.headers : [];
  dynamicColumns = Array.isArray(sheet.columns) ? sheet.columns : [];
  
  // Restore cell formatting if available
  if (sheet.cell_formats) {
    if (typeof sheet.cell_formats === 'string') {
      try {
        cellFormats = JSON.parse(sheet.cell_formats);
      } catch (e) {
        console.error('Failed to parse cell formats:', e);
        cellFormats = {};
      }
    } else {
      cellFormats = sheet.cell_formats || {};
    }
  } else {
    cellFormats = {};
  }
  
  // Ensure data is an array
  let data = sheet.data || [];
  if (!Array.isArray(data)) {
    // If data is a string, try to parse it
    try {
      data = typeof data === 'string' ? JSON.parse(data) : [];
    } catch (e) {
      console.error('Failed to parse sheet data:', e);
      data = [];
    }
  }
  
  // Ensure data is an array of objects
  if (!Array.isArray(data)) {
    data = [];
  }
  
  if (data.length === 0 && columnHeaders.length === 0) {
    // Create default empty grid with A-Z columns and default rows
    columnHeaders = [];
    dynamicColumns = [];
    for (let i = 0; i < 10; i++) {
      const colName = String.fromCharCode(65 + i); // A, B, C, ...
      columnHeaders.push(colName);
      dynamicColumns.push({ data: colName, type: 'text', width: 120 });
    }
    
    // Create default empty rows (20 rows)
    data = [];
    for (let row = 0; row < 20; row++) {
      const rowData = { _id: null };
      columnHeaders.forEach(header => {
        rowData[header] = '';
      });
      data.push(rowData);
    }
    
    sheet.headers = columnHeaders;
    sheet.columns = dynamicColumns;
    sheet.data = data;
  } else if (data.length === 0 && columnHeaders.length > 0) {
    // If we have columns but no rows, create default empty rows
    data = [];
    for (let row = 0; row < 20; row++) {
      const rowData = { _id: null };
      columnHeaders.forEach(header => {
        rowData[header] = '';
      });
      data.push(rowData);
    }
    sheet.data = data;
  }
  
  renderGrid(data, columnHeaders, dynamicColumns);
  // Mark as saved after grid is rendered (to avoid triggering afterChange during load)
  setTimeout(() => {
    markSheetSaved(sheet);
  }, 100);
}

function markSheetUnsaved() {
  if (sheets[currentSheetIndex]) {
    sheets[currentSheetIndex].unsaved = true;
    updateSheetTabs();
  }
}

function markSheetSaved(sheet) {
  if (sheet) {
    sheet.unsaved = false;
    updateSheetTabs();
  }
}

async function loadSavedSheets() {
  try {
    const search = document.getElementById('modalSheetSearch')?.value || '';
    const deptId = document.getElementById('modalSheetDeptFilter')?.value || '';
    const year = document.getElementById('modalSheetYearFilter')?.value || '';
    const params = new URLSearchParams();
    if (search) params.append('search', search);
    if (deptId) params.append('department_id', deptId);
    if (year) params.append('fiscal_year', year);
    
    const res = await fetch(`../ajax/get_saved_sheets.php?${params.toString()}`);
    if (!res.ok) return;
    
    const result = await res.json();
    if (result.success) {
      displaySavedSheets(result.sheets || []);
      // Populate department filter if not already populated
      populateFilters(result.departments || [], result.years || []);
    }
  } catch (e) {
    console.error('Load saved sheets error:', e);
  }
}

function populateFilters(departments, years) {
  // Populate department filter
  const deptFilter = document.getElementById('modalSheetDeptFilter');
  if (deptFilter && deptFilter.children.length <= 1) {
    departments.forEach(dept => {
      const option = document.createElement('option');
      option.value = dept.id;
      option.textContent = dept.name;
      deptFilter.appendChild(option);
    });
  }
  
  // Populate year filter
  const yearFilter = document.getElementById('modalSheetYearFilter');
  if (yearFilter && yearFilter.children.length <= 1) {
    const uniqueYears = [...new Set(years)].sort((a, b) => b - a);
    uniqueYears.forEach(year => {
      const option = document.createElement('option');
      option.value = year;
      option.textContent = year;
      yearFilter.appendChild(option);
    });
  }
}

function displaySavedSheets(sheetsList) {
  const container = document.getElementById('modalSavedSheetsList');
  const countElement = document.getElementById('modalSheetCount');
  
  if (!container) return;
  
  if (sheetsList.length === 0) {
    container.innerHTML = `
      <div class="col-span-full text-center py-12">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="text-gray-500 text-lg font-medium">No saved sheets found</p>
        <p class="text-gray-400 text-sm mt-2">Try adjusting your search or filters</p>
      </div>
    `;
    if (countElement) countElement.textContent = '0 sheets found';
    return;
  }
  
  container.innerHTML = sheetsList.map(sheet => `
    <div class="border border-gray-200 rounded-xl p-5 hover:shadow-lg transition-all duration-200 hover:border-maroon bg-white">
      <div class="flex items-start justify-between mb-3">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-maroon flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="font-semibold text-gray-900 truncate" title="${sheet.sheet_name}">${sheet.sheet_name}</h3>
          </div>
        </div>
      </div>
      <div class="space-y-2 mb-4">
        <div class="flex items-center gap-2 text-sm text-gray-600">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
          </svg>
          <span class="truncate">${sheet.dept_name || 'N/A'}</span>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-600">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
          <span>Fiscal Year: ${sheet.fiscal_year}</span>
        </div>
        <div class="flex items-center gap-2 text-xs text-gray-500">
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span>Updated: ${new Date(sheet.updated_at).toLocaleDateString()}</span>
        </div>
      </div>
      <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
        <button onclick="loadSheetFromModal(${sheet.id})" class="flex-1 px-4 py-2 bg-maroon text-white rounded-lg hover:bg-maroon-dark transition-colors font-medium flex items-center justify-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
          </svg>
          Open
        </button>
        <button onclick="deleteSheetFromModal(${sheet.id})" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
          </svg>
        </button>
      </div>
    </div>
  `).join('');
  
  if (countElement) {
    countElement.textContent = `${sheetsList.length} sheet${sheetsList.length !== 1 ? 's' : ''} found`;
  }
}

function openFileExplorerModal() {
  const modal = document.getElementById('fileExplorerModal');
  if (modal) {
    document.body.classList.add('modal-open');
    modal.classList.remove('hidden');
    loadSavedSheets();
  }
}

function closeFileExplorerModal() {
  const modal = document.getElementById('fileExplorerModal');
  if (modal) {
    document.body.classList.remove('modal-open');
    modal.classList.add('hidden');
    
    // Clear search and filters
    const searchInput = document.getElementById('modalSheetSearch');
    const deptFilter = document.getElementById('modalSheetDeptFilter');
    const yearFilter = document.getElementById('modalSheetYearFilter');
    
    if (searchInput) searchInput.value = '';
    if (deptFilter) deptFilter.value = '';
    if (yearFilter) yearFilter.value = '';
  }
}

async function loadSheetFromModal(sheetId) {
  await loadSheet(sheetId);
  closeFileExplorerModal();
}

async function deleteSheetFromModal(sheetId) {
  if (confirm('Are you sure you want to delete this sheet? This action cannot be undone.')) {
    await deleteSheet(sheetId);
    loadSavedSheets();
  }
}

async function loadSheet(sheetId) {
  try {
    const res = await fetch(`../ajax/load_sheet.php?sheet_id=${sheetId}`);
    if (!res.ok) {
      showError('Failed to load sheet');
      return;
    }
    
    const result = await res.json();
    if (result.success) {
      const sheetData = result.sheet;
      
      // Ensure data, headers, and columns are arrays
      let data = sheetData.data || [];
      if (!Array.isArray(data)) {
        try {
          data = typeof data === 'string' ? JSON.parse(data) : [];
        } catch (e) {
          console.error('Failed to parse data:', e);
          data = [];
        }
      }
      
      let headers = sheetData.headers || [];
      if (!Array.isArray(headers)) {
        try {
          headers = typeof headers === 'string' ? JSON.parse(headers) : [];
        } catch (e) {
          console.error('Failed to parse headers:', e);
          headers = [];
        }
      }
      
      let columns = sheetData.columns || [];
      if (!Array.isArray(columns)) {
        try {
          columns = typeof columns === 'string' ? JSON.parse(columns) : [];
        } catch (e) {
          console.error('Failed to parse columns:', e);
          columns = [];
        }
      }
      
      // Parse cell formats if available
      let cellFormatsData = {};
      if (sheetData.cell_formats) {
        if (typeof sheetData.cell_formats === 'string') {
          try {
            cellFormatsData = JSON.parse(sheetData.cell_formats);
          } catch (e) {
            console.error('Failed to parse cell formats:', e);
          }
        } else {
          cellFormatsData = sheetData.cell_formats || {};
        }
      }
      
      const newSheet = {
        id: sheetData.id,
        name: sheetData.sheet_name,
        data: data,
        headers: headers,
        columns: columns,
        cell_formats: cellFormatsData,
        saved: true,
        unsaved: false,
        department_id: sheetData.department_id,
        fiscal_year: sheetData.fiscal_year
      };
      
      if (sheets.length > 0 && sheets[currentSheetIndex]?.unsaved) {
        pendingSheetSwitch = () => {
          sheets.push(newSheet);
          currentSheetIndex = sheets.length - 1;
          updateSheetTabs();
          loadSheetIntoGrid(newSheet);
        };
        document.getElementById('unsavedModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
      } else {
        sheets.push(newSheet);
        currentSheetIndex = sheets.length - 1;
        updateSheetTabs();
        loadSheetIntoGrid(newSheet);
      }
    }
  } catch (e) {
    console.error('Load sheet error:', e);
    showError('Failed to load sheet: ' + e.message);
  }
}

function openSaveSheetModal() {
  const sheet = sheets[currentSheetIndex];
  if (!sheet) return;
  
  document.getElementById('sheetNameInput').value = sheet.name || '';
  document.getElementById('sheetDeptInput').value = sheet.department_id || '';
  document.getElementById('saveSheetModal').classList.remove('hidden');
  document.body.classList.add('modal-open');
}

function closeSaveSheetModal() {
  document.getElementById('saveSheetModal').classList.add('hidden');
  document.getElementById('saveSheetForm').reset();
  document.body.classList.remove('modal-open');
}

async function handleSaveSheet(e) {
  e.preventDefault();
  const sheet = sheets[currentSheetIndex];
  if (!sheet || !handsontable) return;
  
  const sheetName = document.getElementById('sheetNameInput').value.trim();
  const departmentId = document.getElementById('sheetDeptInput').value;
  
  if (!sheetName || !departmentId) {
    showError('Sheet name and department are required');
    return;
  }
  
  const data = handsontable.getSourceData();
  const formData = new FormData();
  formData.append('sheet_name', sheetName);
  formData.append('department_id', departmentId);
  formData.append('fiscal_year', new Date().getFullYear()); // Use current year as default
  formData.append('headers', JSON.stringify(columnHeaders));
  formData.append('columns', JSON.stringify(dynamicColumns));
  formData.append('data', JSON.stringify(data));
  formData.append('cell_formats', JSON.stringify(cellFormats)); // Save formatting
  if (sheet.id) formData.append('sheet_id', sheet.id);
  
  try {
    const res = await fetch('../ajax/save_sheet.php', { method: 'POST', body: formData });
    const result = await res.json();
    
    if (result.success) {
      sheet.id = result.sheet_id;
      sheet.name = sheetName;
      sheet.department_id = parseInt(departmentId);
      sheet.fiscal_year = new Date().getFullYear(); // Use current year
      sheet.data = data;
      sheet.headers = columnHeaders;
      sheet.columns = dynamicColumns;
      sheet.cell_formats = cellFormats; // Store formatting with sheet
      markSheetSaved(sheet);
      closeSaveSheetModal();
      showSuccess('Sheet saved successfully');
      loadSavedSheets();
    } else {
      showError(result.message || 'Failed to save sheet');
    }
  } catch (e) {
    console.error('Save sheet error:', e);
    showError('Error saving sheet');
  }
}

async function deleteSheet(sheetId) {
  if (!confirm('Are you sure you want to delete this sheet?')) return;
  
  try {
    const formData = new FormData();
    formData.append('sheet_id', sheetId);
    const res = await fetch('../ajax/delete_sheet.php', { method: 'POST', body: formData });
    const result = await res.json();
    
    if (result.success) {
      showSuccess('Sheet deleted successfully');
      loadSavedSheets();
    } else {
      showError(result.message || 'Failed to delete sheet');
    }
  } catch (e) {
    console.error('Delete sheet error:', e);
    showError('Error deleting sheet');
  }
}

// Unsaved changes modal handlers
function handleUnsavedSave() {
  document.getElementById('unsavedModal').classList.add('hidden');
  document.body.classList.remove('modal-open');
  openSaveSheetModal();
  // After save, execute pending switch
  const originalHandleSave = handleSaveSheet;
  window.handleSaveSheet = async function(e) {
    await originalHandleSave(e);
    if (pendingSheetSwitch) {
      pendingSheetSwitch();
      pendingSheetSwitch = null;
    }
    window.handleSaveSheet = originalHandleSave;
  };
}

function handleUnsavedDiscard() {
  document.getElementById('unsavedModal').classList.add('hidden');
  document.body.classList.remove('modal-open');
  if (sheets[currentSheetIndex]) {
    sheets[currentSheetIndex].unsaved = false;
  }
  if (pendingSheetSwitch) {
    pendingSheetSwitch();
    pendingSheetSwitch = null;
  }
}

function handleUnsavedCancel() {
  document.getElementById('unsavedModal').classList.add('hidden');
  document.body.classList.remove('modal-open');
  pendingSheetSwitch = null;
}

// Check if any sheet has unsaved changes
function hasUnsavedChanges() {
  return sheets.some(sheet => sheet.unsaved === true);
}

// Handle page refresh/close
function handleBeforeUnload(e) {
  if (hasUnsavedChanges()) {
    // Modern browsers require returnValue to be set
    e.preventDefault();
    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
    return e.returnValue;
  }
}

// Intercept navigation links to show save prompt
function interceptNavigationLinks() {
  // Get all navigation links in the sidebar
  const navLinks = document.querySelectorAll('aside nav a, aside a');
  
  navLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      if (hasUnsavedChanges()) {
        e.preventDefault();
        const targetUrl = this.getAttribute('href');
        
        // Show custom modal for navigation
        showNavigationModal(targetUrl);
      }
      // If no unsaved changes, allow normal navigation
    });
  });
  
  // Also intercept browser back/forward buttons
  window.addEventListener('popstate', function(e) {
    if (hasUnsavedChanges()) {
      e.preventDefault();
      showNavigationModal(null);
      // Push state back
      history.pushState(null, '', window.location.href);
    }
  });
}

// Show navigation modal
let pendingNavigation = null;

function showNavigationModal(url) {
  pendingNavigation = url;
  document.getElementById('navigationModal').classList.remove('hidden');
  document.body.classList.add('modal-open');
}

function closeNavigationModal() {
  document.getElementById('navigationModal').classList.add('hidden');
  document.body.classList.remove('modal-open');
  pendingNavigation = null;
}

function handleNavigationSave() {
  closeNavigationModal();
  openSaveSheetModal();
  // After save, execute navigation
  const originalHandleSave = handleSaveSheet;
  window.handleSaveSheet = async function(e) {
    await originalHandleSave(e);
    if (pendingNavigation) {
      window.location.href = pendingNavigation;
    }
    window.handleSaveSheet = originalHandleSave;
  };
}

function handleNavigationDiscard() {
  if (pendingNavigation) {
    window.location.href = pendingNavigation;
  } else {
    closeNavigationModal();
  }
}

function handleNavigationCancel() {
  closeNavigationModal();
  pendingNavigation = null;
}

async function deleteSelectedRows(){
  if (!handsontable) return;
  const selected = handsontable.getSelected();
  if (!selected || selected.length === 0) { 
    showError('No rows selected'); 
    return; 
  }
  
  // Get all selected row indices
  const rowsToDelete = new Set();
  selected.forEach(([r1, c1, r2, c2]) => {
    for (let i = Math.min(r1, r2); i <= Math.max(r1, r2); i++) {
      rowsToDelete.add(i);
    }
  });
  
  const rowIndices = Array.from(rowsToDelete).sort((a, b) => b - a); // Sort descending
  
  // Delete from database first
  for (const rowIndex of rowIndices) {
    const rowData = handsontable.getSourceDataAtRow(rowIndex);
    if (rowData && rowData._id) {
      const form = new FormData();
      form.append('id', rowData._id);
      const res = await fetch('../ajax/delete_allocation.php', { method: 'POST', body: form });
      const j = await res.json();
      if (!j.success) { 
        showError(j.message || 'Delete failed for row ' + (rowIndex + 1)); 
        continue;
      }
    }
  }
  
  // Remove rows from grid (delete from end to preserve indices)
  rowIndices.forEach(rowIndex => {
    const currentData = handsontable.getSourceData();
    currentData.splice(rowIndex, 1);
    handsontable.loadData(currentData);
  });
  
  markSheetUnsaved();
  showSuccess('Selected rows deleted');
}

async function saveAllocation(id, amount){
  try{
    const form = new FormData();
    form.append('id', id);
    form.append('allocated_amount', amount);
    const res = await fetch('../ajax/save_allocation.php', { method:'POST', body: form });
    const data = await res.json();
    if(!data.success){ showError(data.message || 'Save failed'); }
  }catch(e){ console.error(e); showError('Save failed'); }
}

async function createAllocation(department_id, category_id, fiscal_year, allocated_amount){
  try{
    const form = new FormData();
    form.append('department_id', department_id);
    form.append('category_id', category_id);
    form.append('fiscal_year', fiscal_year);
    form.append('allocated_amount', allocated_amount);
    const res = await fetch('../ajax/create_allocation.php', { method:'POST', body: form });
    const data = await res.json();
    if(!data.success){ showError(data.message || 'Create failed'); }
  }catch(e){ console.error(e); showError('Create failed'); }
}

// Utility functions for notifications
function showSuccess(message) {
  // Simple alert for now - you can replace with a better notification system
  alert('Success: ' + message);
}

function showError(message) {
  // Simple alert for now - you can replace with a better notification system
  alert('Error: ' + message);
}

function showLoading(button) {
  button.disabled = true;
  button.textContent = 'Loading...';
}

function hideLoading(button) {
  button.disabled = false;
  button.textContent = button.getAttribute('data-original-text') || 'Import Excel (CSV)';
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

// Logout functionality
function confirmLogout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../auth/logout.php';
    }
}
</script>
</body>
</html>

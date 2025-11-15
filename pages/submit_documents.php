<?php
session_start();

// Require login; allow Procurement and Department/Offices roles to access
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$allowedRoles = ['procurement', 'offices'];
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $allowedRoles, true)) {
    // If role not allowed, send to their dashboard instead of login
    switch ($_SESSION['user_role'] ?? '') {
        case 'budget':
            header('Location: ./admin_dashboard.php');
            break;
        case 'school_admin':
            header('Location: ./school_admin_dashboard.php');
            break;
        default:
            header('Location: ./dept_dashboard.php');
            break;
    }
    exit;
}

require_once __DIR__ . '/../classes/FileSubmission.php';

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$departmentId = isset($_SESSION['department_id']) ? (int)$_SESSION['department_id'] : null;
$username = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';
$userEmail = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
$officeLabel = ($_SESSION['user_role'] === 'procurement') ? 'Procurement Office' : (isset($_SESSION['department_name']) ? $_SESSION['department_name'] : 'Department/Office');

$ppmpOk = false; $libOk = false; $messages = [];
$fileSubmission = new FileSubmission();
$blockUploads = $fileSubmission->userHasOpenSubmission($userId, (int)date('Y'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Require both files server-side too
    $hasPpmp = isset($_FILES['ppmp_file']) && $_FILES['ppmp_file']['error'] === UPLOAD_ERR_OK;
    $hasLib = isset($_FILES['lib_file']) && $_FILES['lib_file']['error'] === UPLOAD_ERR_OK;
    if (!$hasPpmp || !$hasLib) {
        $messages[] = 'Both PPMP and LIB files are required.';
    } else {
        // Already have $fileSubmission above

        // Handle PPMP
        $ppmpDir = __DIR__ . '/../uploads/ppmp/'; if (!is_dir($ppmpDir)) { mkdir($ppmpDir, 0777, true); }
        $ppmpExt = pathinfo($_FILES['ppmp_file']['name'], PATHINFO_EXTENSION);
        $ppmpName = 'PPMP_' . $userId . '_' . time() . '.' . $ppmpExt; $ppmpPath = $ppmpDir . $ppmpName;

        // Handle LIB
        $libDir = __DIR__ . '/../uploads/lib/'; if (!is_dir($libDir)) { mkdir($libDir, 0777, true); }
        $libExt = pathinfo($_FILES['lib_file']['name'], PATHINFO_EXTENSION);
        $libName = 'LIB_' . $userId . '_' . time() . '.' . $libExt; $libPath = $libDir . $libName;

        // Upload both first
        $ppmpUploaded = move_uploaded_file($_FILES['ppmp_file']['tmp_name'], $ppmpPath);
        $libUploaded = move_uploaded_file($_FILES['lib_file']['tmp_name'], $libPath);

        if ($ppmpUploaded && $libUploaded) {
            // Save both
            $ppmpId = $fileSubmission->submitFile($userId, $departmentId, 'PPMP', date('Y'), $_FILES['ppmp_file']['name'], $ppmpPath, $_FILES['ppmp_file']['size'], $_FILES['ppmp_file']['type']);
            $libId = $fileSubmission->submitFile($userId, $departmentId, 'LIB', date('Y'), $_FILES['lib_file']['name'], $libPath, $_FILES['lib_file']['size'], $_FILES['lib_file']['type']);
            $ppmpOk = (bool)$ppmpId; $libOk = (bool)$libId;
            if (!($ppmpOk && $libOk)) { $messages[] = 'Failed to record submissions.'; }
        } else {
            $messages[] = 'Upload failed for one or both files.';
        }
    }
    // After a submission, recompute block flag (new submissions are pending)
    $blockUploads = $fileSubmission->userHasOpenSubmission($userId, (int)date('Y'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BudgetTrack - Submit Documents</title>
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
                    <p class="text-sm text-gray-600 sidebar-text"><?php echo htmlspecialchars($officeLabel); ?></p>
                </div>
                <button onclick="toggleSidebar()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg id="sidebarToggleIcon" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                    </svg>
                </button>
            </div>
            
            <nav class="mt-6">
                <?php 
                $userRole = $_SESSION['user_role'];
                $isProcurement = ($userRole === 'procurement');
                
                // Set dashboard link based on role
                $dashboardLink = $isProcurement ? 'proc_dashboard.php' : 'dept_dashboard.php';
                $trackRequestsLink = $isProcurement ? 'proc_track_requests.php' : 'track_requests.php';
                $reportsLink = $isProcurement ? 'proc_reports.php' : 'budget_report.php';
                $notificationsLink = $isProcurement ? 'proc_notifications.php' : 'notifications.php';
                ?>
                
                <a href="<?php echo $dashboardLink; ?>" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Dashboard</span>
                </a>
                <a href="submit_documents.php" class="flex items-center px-6 py-3 text-maroon bg-red-50 border-r-4 border-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Submit PPMP & LIB</span>
                </a>
                <a href="<?php echo $trackRequestsLink; ?>" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Track Requests</span>
                </a>
                <a href="<?php echo $reportsLink; ?>" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Reports</span>
                </a>
                <a href="<?php echo $notificationsLink; ?>" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 102.828 2.828l6.414 6.414a2 2 0 01-2.828 2.828L4.828 7z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Notifications</span>
                </a>
                <?php if ($isProcurement): ?>
                <a href="proc_announcements.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
                    <svg class="w-5 h-5 sidebar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="sidebar-text ml-3">Announcements</span>
                </a>
                <?php endif; ?>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col" style="margin-left: 256px;">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-maroon">Submit PPMP & LIB</h1>
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
                <!-- Submission Status Messages -->
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                    <?php if ($ppmpOk && $libOk): ?>
                        <div class="mb-6 px-4 py-3 rounded-lg bg-green-50 text-green-700 border border-green-200">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Both PPMP and LIB submitted successfully!
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="mb-6 px-4 py-3 rounded-lg bg-red-50 text-red-700 border border-red-200">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Submission incomplete. <?php echo implode(' ', array_map('htmlspecialchars', $messages)); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Document Submission Form -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-maroon mb-2">Document Submission</h2>
                        <p class="text-gray-600">Upload both PPMP and LIB files to complete your submission. Both files are required.</p>
                        <?php if ($blockUploads): ?>
                            <div class="mt-3 text-sm px-3 py-2 rounded bg-yellow-50 text-yellow-800 border border-yellow-200">
                                You already have a PPMP/LIB submission that is pending. You can upload again once it is approved or if it is rejected.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-6" id="comboForm">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    PPMP File *
                                    <span class="text-gray-500 font-normal">(PDF, DOC, DOCX, XLS, XLSX)</span>
                                </label>
                                <div class="relative">
                                    <input type="file" name="ppmp_file" id="ppmp_file" accept=".pdf,.doc,.docx,.xls,.xlsx" 
                                           class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon focus:border-transparent" required <?php echo $blockUploads ? 'disabled' : ''; ?>>
                                </div>
                                <div id="ppmp-status" class="mt-2 text-sm text-gray-500"></div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    LIB File *
                                    <span class="text-gray-500 font-normal">(PDF, DOC, DOCX, XLS, XLSX)</span>
                                </label>
                                <div class="relative">
                                    <input type="file" name="lib_file" id="lib_file" accept=".pdf,.doc,.docx,.xls,.xlsx" 
                                           class="block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-maroon focus:border-transparent" required <?php echo $blockUploads ? 'disabled' : ''; ?>>
                                </div>
                                <div id="lib-status" class="mt-2 text-sm text-gray-500"></div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                            <a href="<?php echo $dashboardLink; ?>" class="px-6 py-3 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" id="submitBtn" class="px-6 py-3 bg-maroon text-white rounded-lg hover:bg-maroon-dark transition-colors disabled:opacity-50 disabled:cursor-not-allowed" <?php echo $blockUploads ? 'disabled' : ''; ?>>
                                <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                Submit Documents
                            </button>
                        </div>
                    </form>
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

        // File upload functionality
        const ppmpInput = document.getElementById('ppmp_file');
        const libInput = document.getElementById('lib_file');
        const submitBtn = document.getElementById('submitBtn');
        const ppmpStatus = document.getElementById('ppmp-status');
        const libStatus = document.getElementById('lib-status');
        const uploadsBlocked = <?php echo $blockUploads ? 'true' : 'false'; ?>;

        function updateFileStatus(input, statusElement, fileType) {
            if (input.files.length > 0) {
                const file = input.files[0];
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB
                statusElement.innerHTML = `<span class="text-green-600">✓ ${file.name} (${fileSize} MB)</span>`;
            } else {
                statusElement.innerHTML = `<span class="text-gray-500">No ${fileType} file selected</span>`;
            }
        }

        function updateButton() {
            if (uploadsBlocked) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }
            const hasPpmp = ppmpInput.files.length > 0;
            const hasLib = libInput.files.length > 0;
            
            submitBtn.disabled = !(hasPpmp && hasLib);
            
            if (hasPpmp && hasLib) {
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        if (!uploadsBlocked) {
            ppmpInput.addEventListener('change', function() {
                updateFileStatus(ppmpInput, ppmpStatus, 'PPMP');
                updateButton();
            });
            libInput.addEventListener('change', function() {
                updateFileStatus(libInput, libStatus, 'LIB');
                updateButton();
            });
        } else {
            // Show blocked message in status fields
            ppmpStatus.innerHTML = '<span class="text-yellow-700">Submission blocked while your previous PPMP/LIB is pending.</span>';
            libStatus.innerHTML = '<span class="text-yellow-700">Submission blocked while your previous PPMP/LIB is pending.</span>';
        }

        // Initialize
        if (!uploadsBlocked) {
            updateFileStatus(ppmpInput, ppmpStatus, 'PPMP');
            updateFileStatus(libInput, libStatus, 'LIB');
        }
        updateButton();
        
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
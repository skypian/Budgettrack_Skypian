<?php
session_start();

// Check if user is logged in and has permission
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Role.php';
require_once __DIR__ . '/../config/database.php';

$user = new User();
$role = new Role();

// Check if user has permission to manage roles
if (!$user->hasPermission($_SESSION['user_id'], 'create_roles')) {
    header('Location: ../pages/dashboard.php');
    exit;
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_role':
                $new_role = new Role();
                $new_role->role_name = trim($_POST['role_name']);
                $new_role->role_description = trim($_POST['role_description']);

                if (empty($new_role->role_name)) {
                    $error = 'Role name is required.';
                } elseif ($role->roleNameExists($new_role->role_name)) {
                    $error = 'Role name already exists.';
                } else {
                    if ($new_role->create()) {
                        $message = 'Role created successfully.';
                    } else {
                        $error = 'Failed to create role.';
                    }
                }
                break;

            case 'update_role':
                $update_role = new Role();
                $update_role->id = $_POST['role_id'];
                $update_role->role_name = trim($_POST['role_name']);
                $update_role->role_description = trim($_POST['role_description']);

                if (empty($update_role->role_name)) {
                    $error = 'Role name is required.';
                } elseif ($role->roleNameExists($update_role->role_name, $update_role->id)) {
                    $error = 'Role name already exists.';
                } else {
                    if ($update_role->update()) {
                        $message = 'Role updated successfully.';
                    } else {
                        $error = 'Failed to update role.';
                    }
                }
                break;

            case 'delete_role':
                $delete_role = new Role();
                $delete_role->id = $_POST['role_id'];
                if ($delete_role->delete()) {
                    $message = 'Role deleted successfully.';
                } else {
                    $error = 'Failed to delete role.';
                }
                break;

            case 'update_permissions':
                $role_id = $_POST['role_id'];
                $permissions = isset($_POST['permissions']) ? $_POST['permissions'] : [];
                
                // Get database connection
                $db = getDB();
                
                // Delete existing permissions for this role
                $delete_query = "DELETE FROM role_permissions WHERE role_id = :role_id";
                $delete_stmt = $db->prepare($delete_query);
                $delete_stmt->bindParam(':role_id', $role_id);
                $delete_stmt->execute();
                
                // Insert new permissions
                if (!empty($permissions)) {
                    $insert_query = "INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)";
                    $insert_stmt = $db->prepare($insert_query);
                    
                    foreach ($permissions as $permission_id) {
                        $insert_stmt->bindParam(':role_id', $role_id);
                        $insert_stmt->bindParam(':permission_id', $permission_id);
                        $insert_stmt->execute();
                    }
                }
                
                $message = 'Role permissions updated successfully.';
                break;
        }
    }
}

// Get all roles and permissions
$roles = $role->getAllRoles();

// Get all permissions grouped by module
$db = getDB();
$permissions_query = "SELECT * FROM permissions ORDER BY module, permission_name";
$permissions_stmt = $db->prepare($permissions_query);
$permissions_stmt->execute();
$all_permissions = $permissions_stmt->fetchAll(PDO::FETCH_ASSOC);

// Group permissions by module
$permissions_by_module = [];
foreach ($all_permissions as $permission) {
    $permissions_by_module[$permission['module']][] = $permission;
}

// Get role permissions for editing
$role_permissions = [];
if (isset($_GET['edit_permissions'])) {
    $role_id = $_GET['edit_permissions'];
    $permissions_query = "SELECT permission_id FROM role_permissions WHERE role_id = :role_id";
    $permissions_stmt = $db->prepare($permissions_query);
    $permissions_stmt->bindParam(':role_id', $role_id);
    $permissions_stmt->execute();
    $role_permissions = array_column($permissions_stmt->fetchAll(PDO::FETCH_ASSOC), 'permission_id');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Management - BudgetTrack</title>
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
<body class="bg-gray-50 font-inter">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg border-r border-gray-200 relative">
            <div class="p-6 border-b border-gray-200">
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
                <a href="allocations.php" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-50 hover:text-maroon">
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
                <a href="role_management.php" class="flex items-center px-6 py-3 text-maroon bg-red-50 border-r-4 border-maroon">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1721 9z"></path>
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
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-maroon">Role Management</h1>
                        <p class="text-gray-600">Create and manage user roles and permissions</p>
                </div>
                <div class="flex items-center space-x-4">
                        <button onclick="goBack()" class="px-4 py-2 text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50">
                            <i class="fas fa-arrow-left mr-2"></i>Back
                        </button>
                        <div class="relative">
                            <button onclick="toggleProfileDropdown()" class="flex items-center space-x-3 bg-gray-50 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-10 h-10 bg-maroon rounded-full flex items-center justify-center text-white font-semibold">
                                    <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
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
        <!-- Messages -->
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center space-x-4">
                <button onclick="goBack()" class="flex items-center text-gray-600 hover:text-gray-800 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </button>
                <h2 class="text-2xl font-bold text-gray-800">Role Management</h2>
            </div>
            <button onclick="openCreateModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded flex items-center">
                <i class="fas fa-plus mr-2"></i> Create New Role
            </button>
        </div>

        <!-- Roles Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($roles as $r): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($r['role_name']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($r['role_description']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M d, Y', strtotime($r['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($r)); ?>)" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="?edit_permissions=<?php echo $r['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-key"></i>
                            </a>
                            <button onclick="deleteRole(<?php echo $r['id']; ?>)" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Permissions Management -->
        <?php if (isset($_GET['edit_permissions'])): ?>
        <?php 
        $editing_role = $roles[array_search($_GET['edit_permissions'], array_column($roles, 'id'))];
        $is_editing_admin = ($editing_role['role_name'] === 'admin');
        ?>
        <div class="mt-8 bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Manage Permissions for Role: <?php echo htmlspecialchars($editing_role['role_name']); ?></h3>
                <?php if ($is_editing_admin): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span class="text-sm font-medium">Admin Role - Use with caution!</span>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($is_editing_admin): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Warning: Modifying Admin Permissions</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>You are modifying the admin role permissions. Changes here will affect all admin users immediately. Be very careful about removing permissions as this could lock out admin users from important system functions.</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_permissions">
                <input type="hidden" name="role_id" value="<?php echo $_GET['edit_permissions']; ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($permissions_by_module as $module => $module_permissions): ?>
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-3 capitalize"><?php echo str_replace('_', ' ', $module); ?></h4>
                        <div class="space-y-2">
                            <?php foreach ($module_permissions as $permission): ?>
                            <label class="flex items-center">
                                <input type="checkbox" name="permissions[]" value="<?php echo $permission['id']; ?>" 
                                       <?php echo in_array($permission['id'], $role_permissions) ? 'checked' : ''; ?>
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-700"><?php echo htmlspecialchars($permission['permission_name']); ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="role_management.php" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                        Update Permissions
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Create Role Modal -->
    <div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create_role">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Create New Role</h3>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role Name *</label>
                            <input type="text" name="role_name" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="role_description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                        <button type="button" onclick="closeCreateModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                            Create Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_role">
                    <input type="hidden" name="role_id" id="edit_role_id">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Edit Role</h3>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Role Name *</label>
                            <input type="text" name="role_name" id="edit_role_name" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="role_description" id="edit_role_description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                            Update Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="delete_role">
                    <input type="hidden" name="role_id" id="delete_role_id">
                    <div class="px-6 py-4">
                        <h3 class="text-lg font-medium text-gray-900">Confirm Delete</h3>
                        <p class="mt-2 text-sm text-gray-500">Are you sure you want to delete this role? This action cannot be undone.</p>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                        <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700">
                            Delete Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openCreateModal() {
            document.getElementById('createModal').classList.remove('hidden');
        }

        function closeCreateModal() {
            document.getElementById('createModal').classList.add('hidden');
        }

        function openEditModal(role) {
            document.getElementById('edit_role_id').value = role.id;
            document.getElementById('edit_role_name').value = role.role_name;
            document.getElementById('edit_role_description').value = role.role_description || '';
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function deleteRole(roleId) {
            document.getElementById('delete_role_id').value = roleId;
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const createModal = document.getElementById('createModal');
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === createModal) {
                closeCreateModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
            if (event.target === deleteModal) {
                closeDeleteModal();
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
            const button = event.target.closest('button');
            
            if (!button || !button.onclick || button.onclick.toString().indexOf('toggleProfileDropdown') === -1) {
                dropdown.classList.add('hidden');
            }
        });

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
    </script>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Confirm Logout</h3>
                    <button onclick="closeLogoutModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="px-6 py-4">
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
    </div>
</body>
</html>

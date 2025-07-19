<?php
session_start();
require_once '../includes/config.php';

// Verify admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';

// Handle delete action
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    try {
        // Only prevent deleting self
        if ($user_id == $_SESSION['user_id']) {
            $_SESSION['error'] = "You cannot delete your own account";
            header("Location: users.php");
            exit();
        }

        $stmt = $link->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "User deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting user";
        }
        header("Location: users.php");
        exit();
    } catch (Exception $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Check for success message from redirect
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Check for error message from redirect
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch all users separated by role
$admins = $link->query("SELECT * FROM users WHERE role = 'admin' ORDER BY created_at DESC");
$regular_users = $link->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC");
?>

<?php include 'header.php'; ?>

<div class="admin-main">
    <div class="dashboard-container">
        <!-- Success/Error Messages -->
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
                <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>

        <!-- Admin Users Section -->
        <div class="users-section admin-users">
            <h2>Admin Users</h2>
            
            <?php if ($admins->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $admins->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No admin users found.</p>
            <?php endif; ?>
        </div>

        <!-- Regular Users Section -->
        <div class="users-section regular-users">
            <h2>Regular Users</h2>
            
            <?php if ($regular_users->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $regular_users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="users.php?delete=<?php echo $row['user_id']; ?>" class="action-btn delete" 
                                       onclick="return confirm('WARNING: This will permanently delete the user. Continue?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No regular users found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<?php
session_start();
require_once '../includes/config.php';

// Verify user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';

// Get current user data
$user_id = $_SESSION['user_id'];
$result = $link->query("SELECT * FROM users WHERE user_id = {$user_id}");
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Validate inputs
        if (empty($name) || empty($email)) {
            $error = "Name and email are required";
        } else {
            try {
                // Update user
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $link->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE user_id = ?");
                    $stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
                } else {
                    $stmt = $link->prepare("UPDATE users SET name = ?, email = ? WHERE user_id = ?");
                    $stmt->bind_param("ssi", $name, $email, $user_id);
                }

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Profile updated successfully!";
                    header("Location: profile.php");
                    exit();
                } else {
                    $error = "Error updating profile. Please try again.";
                }
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
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

        <div class="profile-section">
            <div class="profile-header">
                <h2>My Profile</h2>
            </div>
            
            <div class="profile-form">
                <form method="POST">
                    <div class="form-group">
                        <label for="name">Name*</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo htmlspecialchars($user['name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email*</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password">
                        <small class="form-text text-muted">Leave blank to keep current password</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" value="<?php echo ucfirst($user['role']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>Joined</label>
                        <input type="text" value="<?php echo date('M j, Y', strtotime($user['created_at'])); ?>" disabled>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
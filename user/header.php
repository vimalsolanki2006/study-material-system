<?php
if (!isset($_SESSION)) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard | Study Material System</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/user.css">
</head>
<body>
    <header class="admin-header">
        <nav class="admin-nav">
            <div class="admin-brand">Study Material System</div>
            <div class="admin-nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="courses_materials.php">Download Material</a>
                <a href="profile.php">My Profile</a>
            </div>
            <div class="admin-user">
                <div class="welcome-message">
                    Welcome, <span class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                </div>
                <a href="../logout.php" class="logout-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </header>
    <main class="admin-main">
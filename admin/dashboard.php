<?php
session_start();
require_once '../includes/config.php';

// Including header of the admin role
include 'header.php';

// Fetch statistics
$courses_count = $link->query("SELECT COUNT(*) FROM courses")->fetch_row()[0];
$active_courses = $link->query("SELECT COUNT(*) FROM courses WHERE is_active = 1")->fetch_row()[0];
$materials_count = $link->query("SELECT COUNT(*) FROM materials")->fetch_row()[0];
$users_count = $link->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$students_count = $link->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetch_row()[0];
$admins_count = $link->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetch_row()[0];

// Fetch recent activities
$recent_courses = $link->query("SELECT course_id, course_name, created_at FROM courses ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recent_materials = $link->query("SELECT m.title, c.course_name, m.upload_date FROM materials m JOIN courses c ON m.course_id = c.course_id ORDER BY m.upload_date DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recent_users = $link->query("SELECT user_id, name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<div class="dashboard-container">
    <h1 class="dashboard-title">Admin Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Courses</h3>
            <p><?php echo $courses_count; ?></p>
            <small><?php echo $active_courses; ?> active</small>
        </div>
        
        <div class="stat-card">
            <h3>Study Materials</h3>
            <p><?php echo $materials_count; ?></p>
            <small>Total uploads</small>
        </div>
        
        <div class="stat-card">
            <h3>Registered Users</h3>
            <p><?php echo $users_count; ?></p>
            <small><?php echo $students_count; ?> students, <?php echo $admins_count; ?> admins</small>
        </div>
    </div>
    
    <div class="quick-actions">
        <a href="courses.php" class="action-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
            </svg>
            Manage Courses
        </a>
        <a href="materials.php" class="action-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            Manage Materials
        </a>
        <a href="users.php" class="action-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            Manage Users
        </a>
    </div>
    
    <div class="activity-sections">
        <section class="recent-activity">
            <h3>Recent Courses</h3>
            <ul>
                <?php foreach ($recent_courses as $course): ?>
                <li>
                    <span><?php echo htmlspecialchars($course['course_name']); ?></span>
                    <small>Created: <?php echo date('M j, Y', strtotime($course['created_at'])); ?></small>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        
        <section class="recent-activity">
            <h3>Recent Materials</h3>
            <ul>
                <?php foreach ($recent_materials as $material): ?>
                <li>
                    <span><?php echo htmlspecialchars($material['title']); ?></span>
                    <small><?php echo htmlspecialchars($material['course_name']); ?> • <?php echo date('M j', strtotime($material['upload_date'])); ?></small>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        
        <section class="recent-activity">
            <h3>New Users</h3>
            <ul>
                <?php foreach ($recent_users as $user): ?>
                <li>
                    <span><?php echo htmlspecialchars($user['name']); ?></span>
                    <small><?php echo htmlspecialchars($user['email']); ?></small>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>
</div>

<?php include 'footer.php'; ?>
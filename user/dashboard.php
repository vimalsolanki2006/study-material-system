<?php
session_start();
require_once '../includes/config.php';

// Verify user access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Include user header
include 'header.php';

// Fetch statistics for user dashboard
$active_courses = $link->query("SELECT COUNT(*) FROM courses WHERE is_active = 1")->fetch_row()[0];
$total_materials = $link->query("SELECT COUNT(*) FROM materials WHERE is_active = 1")->fetch_row()[0];

// Fetch recent activities for user
$recent_courses = $link->query("SELECT course_id, course_name, created_at FROM courses WHERE is_active = 1 ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recent_materials = $link->query("SELECT m.title, c.course_name, m.upload_date FROM materials m JOIN courses c ON m.course_id = c.course_id WHERE m.is_active = 1 ORDER BY m.upload_date DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$popular_courses = $link->query("SELECT c.course_id, c.course_name, COUNT(m.material_id) as material_count FROM courses c LEFT JOIN materials m ON c.course_id = m.course_id WHERE c.is_active = 1 GROUP BY c.course_id ORDER BY material_count DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
?>

<div class="dashboard-container">
    <h1 class="dashboard-title">User Dashboard</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Available Courses</h3>
            <p><?php echo $active_courses; ?></p>
            <small>Active courses</small>
        </div>
        
        <div class="stat-card">
            <h3>Study Materials</h3>
            <p><?php echo $total_materials; ?></p>
            <small>Total available</small>
        </div>
        
    </div>
    
    <div class="quick-actions">
        <a href="courses_materials.php" class="action-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            Download Material
        </a>
        <a href="profile.php" class="action-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            My Profile
        </a>
    </div>
    
    <div class="activity-sections">
        <section class="recent-activity">
            <h3>New Courses</h3>
            <ul>
                <?php foreach ($recent_courses as $course): ?>
                <li>
                    <span><?php echo htmlspecialchars($course['course_name']); ?></span>
                    <small>Added: <?php echo date('M j, Y', strtotime($course['created_at'])); ?></small>
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
            <h3>Popular Courses</h3>
            <ul>
                <?php foreach ($popular_courses as $course): ?>
                <li>
                    <span><?php echo htmlspecialchars($course['course_name']); ?></span>
                    <small><?php echo $course['material_count']; ?> materials</small>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </div>
</div>

<?php include 'footer.php'; ?>
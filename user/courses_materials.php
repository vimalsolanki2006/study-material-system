<?php
session_start();
require_once '../includes/config.php';

// Verify user access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// Get selected course (if any)
$selected_course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;

// Fetch all active courses
$courses = $link->query("SELECT * FROM courses WHERE is_active = 1 ORDER BY course_name")->fetch_all(MYSQLI_ASSOC);

// Fetch materials for selected course (if any)
$materials = [];
if ($selected_course_id) {
    $stmt = $link->prepare("SELECT m.*, c.course_name 
                          FROM materials m 
                          JOIN courses c ON m.course_id = c.course_id
                          WHERE m.course_id = ? AND m.is_active = 1
                          ORDER BY m.upload_date DESC");
    $stmt->bind_param('i', $selected_course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $materials = $result->fetch_all(MYSQLI_ASSOC);
}

include 'header.php';
?>

<div class="admin-main">
    <div class="dashboard-container">
        <h1 class="page-title">Study Materials</h1>
        
        <!-- Course Selection Section -->
        <div class="course-selection">
            <h2>Select a Course</h2>
            <div class="courses-grid">
                <?php if (empty($courses)): ?>
                    <div class="alert alert-info">No courses available at the moment.</div>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <a href="courses_materials.php?course_id=<?= $course['course_id'] ?>" 
                           class="course-card <?= $selected_course_id == $course['course_id'] ? 'active' : '' ?>">
                            <div class="course-card-header">
                                <h3><?= htmlspecialchars($course['course_name']) ?></h3>
                            </div>
                            <div class="course-card-body">
                                <?php 
                                    $stmt = $link->prepare("SELECT COUNT(*) FROM materials WHERE course_id = ? AND is_active = 1");
                                    $stmt->bind_param('i', $course['course_id']);
                                    $stmt->execute();
                                    $stmt->bind_result($material_count);
                                    $stmt->fetch();
                                    $stmt->close();
                                ?>
                                <p><?= $material_count ?> materials available</p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Materials Section -->
        <?php if ($selected_course_id): ?>
            <div class="materials-section">
                <h2><?= htmlspecialchars($materials[0]['course_name'] ?? 'Selected Course') ?> Materials</h2>
                
                <?php if (empty($materials)): ?>
                    <div class="alert alert-info">No materials available for this course yet.</div>
                <?php else: ?>
                    <div class="materials-list">
                        <?php foreach ($materials as $material): ?>
                            <div class="material-item">
                                <div class="material-info">
                                    <h3><?= htmlspecialchars($material['title']) ?></h3>
                                    <p class="material-description"><?= htmlspecialchars($material['description']) ?></p>
                                    <div class="material-meta">
                                        <span class="file-type"><?= htmlspecialchars($material['file_type']) ?></span>
                                        <span class="file-size"><?= formatFileSize($material['file_size']) ?></span>
                                        <span class="upload-date">Uploaded: <?= date('M d, Y', strtotime($material['upload_date'])) ?></span>
                                    </div>
                                </div>
                                <a href="download.php?path=../uploads/materials/<?= $material['file_path'] ?>" class="download-btn">
                                    Download
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="instruction-box">
                <p>Please select a course from above to view its available study materials.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}

include 'footer.php'; 
?>
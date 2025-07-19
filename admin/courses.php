<?php
session_start();
require_once '../includes/config.php';

// authenticating that only admin can access it
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';
$course = ['course_id' => '', 'course_name' => '', 'description' => '', 'is_active' => 1];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add/Update Course
    if (isset($_POST['save_course'])) {
        $course_id = $_POST['course_id'] ?? 0;
        $course_name = trim($_POST['course_name']);
        $description = trim($_POST['description']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // Validate inputs
        if (empty($course_name)) {
            $error = "Course name is required";
        } else {
            try {
                if ($course_id > 0) {
                    // Update existing course
                    $stmt = $link->prepare("UPDATE courses SET course_name = ?, description = ?, is_active = ?, updated_at = NOW() WHERE course_id = ?");
                    $stmt->bind_param("ssii", $course_name, $description, $is_active, $course_id);
                    $action = "updated";
                } else {
                    // Add new course
                    $stmt = $link->prepare("INSERT INTO courses (course_name, description, is_active, created_by, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->bind_param("ssii", $course_name, $description, $is_active, $_SESSION['user_id']);
                    $action = "added";
                }

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Course {$action} successfully!";
                    header("Location: courses.php");
                    exit();
                } else {
                    $error = "Error saving course. Please try again.";
                }
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Handling course deactivation action
if (isset($_GET['deactivate'])) {
    $course_id = (int)$_GET['deactivate'];
    try {
        $stmt = $link->prepare("UPDATE courses SET is_active = 0 WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Course deactivated successfully!";
            header("Location: courses.php");
            exit();
        }
    } catch (Exception $e) {
        $error = "Error deactivating course: " . $e->getMessage();
    }
}

// Handle course activation action
if (isset($_GET['activate'])) {
    $course_id = (int)$_GET['activate'];
    try {
        $stmt = $link->prepare("UPDATE courses SET is_active = 1 WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Course activated successfully!";
            header("Location: courses.php");
            exit();
        }
    } catch (Exception $e) {
        $error = "Error activating course: " . $e->getMessage();
    }
}

// Handling course delete action
if (isset($_GET['delete'])) {
    $course_id = (int)$_GET['delete'];
    try {
        // First check if the course has any materials
        $material_check = $link->query("SELECT COUNT(*) FROM materials WHERE course_id = $course_id")->fetch_row()[0];
        
        if ($material_check > 0) {
            $_SESSION['error'] = "Cannot delete course - it has associated materials. Deactivate it instead.";
            header("Location: courses.php");
            exit();
        }

        $stmt = $link->prepare("DELETE FROM courses WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Course permanently deleted successfully!";
            header("Location: courses.php");
            exit();
        }
    } catch (Exception $e) {
        $error = "Error deleting course: " . $e->getMessage();
    }
}

// Handling view course action
if (isset($_GET['view'])) {
    $course_id = (int)$_GET['view'];
    $result = $link->query("SELECT * FROM courses WHERE course_id = {$course_id}");
    if ($result->num_rows > 0) {
        $course = $result->fetch_assoc();
        $view_mode = true;
    }
}

// Handling course edit action
if (isset($_GET['edit'])) {
    $course_id = (int)$_GET['edit'];
    $result = $link->query("SELECT * FROM courses WHERE course_id = {$course_id}");
    if ($result->num_rows > 0) {
        $course = $result->fetch_assoc();
    }
}

// Checking for success message from redirection
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Checking for error message from redirection
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetching all courses from the database course table
$courses = $link->query("SELECT * FROM courses ORDER BY created_at DESC");
?>

<?php include 'header.php'; ?>

<div class="admin-main">
    <div class="dashboard-container">
        <!-- Displaying Error if any Messages -->
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
                <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>
        
        <!-- Displaying Success if any Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>

        <!-- View Course Section -->
        <?php if (isset($view_mode)): ?>
            <div class="course-view">
                <div class="view-header">
                    <h2><?php echo htmlspecialchars($course['course_name']); ?></h2>
                    <a href="courses.php" class="btn btn-secondary">Back to Courses</a>
                </div>
                
                <div class="course-details">
                    <p><strong>Description:</strong></p>
                    <div class="description-box"><?php echo nl2br(htmlspecialchars($course['description'])); ?></div>
                    
                    <p><strong>Status:</strong> 
                        <span class="status-badge <?php echo $course['is_active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $course['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </p>
                    
                    <div class="action-buttons">
                        <a href="courses.php?edit=<?php echo $course['course_id']; ?>" class="btn btn-primary">
                             Edit Course
                        </a>
                        <?php if ($course['is_active']): ?>
                            <a href="courses.php?deactivate=<?php echo $course['course_id']; ?>" class="btn btn-warning" 
                               onclick="return confirm('Are you sure you want to deactivate this course?')">
                                Deactivate
                            </a>
                        <?php else: ?>
                            <a href="courses.php?activate=<?php echo $course['course_id']; ?>" class="btn btn-success">
                                Activate
                            </a>
                            <a href="courses.php?delete=<?php echo $course['course_id']; ?>" class="btn btn-danger" 
                               onclick="return confirm('WARNING: This will permanently delete the course. Continue?')">
                                Delete Permanently
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <!-- Add/Edit Course Section -->
        <?php elseif (isset($_GET['edit']) || !isset($_GET['view'])): ?>
            <div class="course-form">
                <h2><?php echo $course['course_id'] ? 'Edit Course' : 'Add New Course'; ?></h2>
                <form method="POST">
                    <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                    
                    <div class="form-group">
                        <label for="course_name">Course Name*</label>
                        <input type="text" id="course_name" name="course_name" required 
                               value="<?php echo htmlspecialchars($course['course_name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4"><?php 
                            echo htmlspecialchars($course['description']); 
                        ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_active" name="is_active" value="1" <?php 
                                echo $course['is_active'] ? 'checked' : ''; 
                            ?>>
                            <label for="is_active">Active</label>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="save_course" class="btn btn-primary">
                            <?php echo $course['course_id'] ? 'Update Course' : 'Add Course'; ?>
                        </button>
                        
                        <a href="courses.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Courses List Section -->
        <div class="courses-list">
            <h2>Manage Courses</h2>
            
            <?php if ($courses->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Course Name</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $courses->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['course_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $row['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="courses.php?view=<?php echo $row['course_id']; ?>" class="action-btn view">
                                        View
                                    </a>
                                    <a href="courses.php?edit=<?php echo $row['course_id']; ?>" class="action-btn edit">
                                        Edit
                                    </a>
                                    <?php if ($row['is_active']): ?>
                                        <a href="courses.php?deactivate=<?php echo $row['course_id']; ?>" class="action-btn deactivate" 
                                           onclick="return confirm('Are you sure you want to deactivate this course?')">
                                            Deactivate
                                        </a>
                                    <?php else: ?>
                                        <a href="courses.php?activate=<?php echo $row['course_id']; ?>" class="action-btn activate">
                                            Activate
                                        </a>
                                        <a href="courses.php?delete=<?php echo $row['course_id']; ?>" class="action-btn delete" 
                                           onclick="return confirm('WARNING: This will permanently delete the course. Continue?')">
                                            Delete
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No courses found. Add your first course using the form above.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
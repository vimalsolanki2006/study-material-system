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
$materials = [];
$courses = [];

// Fetch all active courses for dropdown menu
$courses_result = $link->query("SELECT course_id, course_name FROM courses WHERE is_active = 1 ORDER BY course_name");
$courses = $courses_result->fetch_all(MYSQLI_ASSOC);

// Handling file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_material'])) {
    $course_id = (int)$_POST['course_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    // Validate inputs
    if (empty($course_id) || empty($title)) {
        $error = "Course and title are required fields";
    } elseif (!isset($_FILES['material_file']) || $_FILES['material_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = "Please select a file to upload";
    } else {
        $file = $_FILES['material_file'];
        
        // File upload validation
        $allowed_types = [
            'application/pdf',
            'application/msword',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];
        $max_size = 10 * 1024 * 1024; // 10MB
        
        if (!in_array($file['type'], $allowed_types)) {
            $error = "Only PDF, DOC, DOCX, PPT, and PPTX files are allowed";
        } elseif ($file['size'] > $max_size) {
            $error = "File size must be less than 10MB";
        } else {
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $upload_path = '../uploads/materials/' . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                // storing the file to database
                $stmt = $link->prepare("INSERT INTO materials (
                    title, description, file_path, file_size, file_type, 
                    course_id, uploaded_by, upload_date, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 1)");
                
                $stmt->bind_param(
                    "sssssii", 
                    $title, 
                    $description,
                    $filename,
                    $file['size'],
                    $file['type'],
                    $course_id,
                    $_SESSION['user_id']
                );
                
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Material uploaded successfully!";
                    header("Location: materials.php");
                    exit();
                } else {
                    $error = "Error saving material to database: " . $link->error;
                    // Delete the uploaded file if DB insert failed
                    unlink($upload_path);
                }
            } else {
                $error = "Error uploading file. Please try again.";
            }
        }
    }
}

// Handle delete action (soft delete)
if (isset($_GET['delete'])) {
    $material_id = (int)$_GET['delete'];
    
    // Soft delete (set is_active = 0)
    if ($link->query("UPDATE materials SET is_active = 0 WHERE material_id = $material_id")) {
        $_SESSION['success'] = "Material deactivated successfully!";
        header("Location: materials.php");
        exit();
    } else {
        $error = "Error deactivating material: " . $link->error;
    }
}

// Handle activate action
if (isset($_GET['activate'])) {
    $material_id = (int)$_GET['activate'];
    
    if ($link->query("UPDATE materials SET is_active = 1 WHERE material_id = $material_id")) {
        $_SESSION['success'] = "Material activated successfully!";
        header("Location: materials.php");
        exit();
    } else {
        $error = "Error activating material: " . $link->error;
    }
}

// Check for messages
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch all materials with course names and uploader info 
$materials_query = "SELECT m.*, c.course_name, u.name as uploaded_by_name 
                   FROM materials m 
                   JOIN courses c ON m.course_id = c.course_id
                   JOIN users u ON m.uploaded_by = u.user_id
                   ORDER BY m.upload_date DESC";
$materials_result = $link->query($materials_query);
$materials = $materials_result->fetch_all(MYSQLI_ASSOC);
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

        <!-- Upload Material Section -->
        <div class="materials-upload">
            <h2>Upload New Material</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="course_id">Course*</label>
                    <select id="course_id" name="course_id" class="file-upload-input" required>
                        <option value="">-- Select Course --</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['course_id']; ?>">
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="title">Title*</label>
                    <input type="text" id="title" name="title" class="file-upload-input" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" class="file-upload-input"></textarea>
                </div>
                
                <div class="file-upload-wrapper">
                    <label class="file-upload-label">Material File*</label>
                    <input type="file" name="material_file" class="file-upload-input" required>
                    <small class="file-upload-hint">Accepted formats: PDF, DOC, DOCX, PPT, PPTX (Max 10MB)</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="upload_material" class="btn btn-primary">
                        Upload Material
                    </button>
                </div>
            </form>
        </div>

        <!-- Materials List Section -->
        <div class="materials-list">
            <h2>Manage Materials</h2>
            
            <?php if (count($materials) > 0): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Course</th>
                                <th>Uploaded By</th>
                                <th>File Info</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($materials as $material): ?>
                            <tr>
                                <td>
                                    <div class="material-title"><?php echo htmlspecialchars($material['title']); ?></div>
                                    <div class="material-meta"><?php echo htmlspecialchars($material['description']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($material['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($material['uploaded_by_name']); ?></td>
                                <td>
                                    <div class="material-meta">
                                        <strong>Type:</strong> <?php echo htmlspecialchars($material['file_type']); ?><br>
                                        <strong>Size:</strong> <?php echo formatFileSize($material['file_size']); ?><br>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $material['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $material['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="../uploads/materials/<?php echo $material['file_path']; ?>" 
                                       class="action-btn view" download>
                                        Download
                                    </a>
                                    <?php if ($material['is_active']): ?>
                                        <a href="materials.php?delete=<?php echo $material['material_id']; ?>" 
                                           class="action-btn deactivate" 
                                           onclick="return confirm('Are you sure you want to deactivate this material?')">
                                            Deactivate
                                        </a>
                                    <?php else: ?>
                                        <a href="materials.php?activate=<?php echo $material['material_id']; ?>" 
                                           class="action-btn activate">
                                            Activate
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No materials found. Upload your first material using the form above.</p>
            <?php endif; ?>
        </div>
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
?>

<?php include 'footer.php'; ?>
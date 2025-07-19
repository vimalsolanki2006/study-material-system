<?php
session_start();
require_once '../includes/config.php';

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if path parameter exists
if(isset($_GET['path'])) {
    // Sanitize the path
    $filename = '../uploads/materials/' . basename($_GET['path']);
    
    // Verify the file exists in our materials directory
    $allowed_path = realpath('../uploads/materials');
    $file_path = realpath($filename);
    
    if($file_path && strpos($file_path, $allowed_path) === 0 && file_exists($file_path)) {
        // Verify the material exists and is active in database
        $material_file = basename($_GET['path']);
        $stmt = $link->prepare("SELECT material_id FROM materials WHERE file_path = ? AND is_active = 1");
        $stmt->bind_param('s', $material_file);
        $stmt->execute();
        $stmt->store_result();
        
        if($stmt->num_rows > 0) {
            // Define header information
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: 0");
            header('Content-Disposition: attachment; filename="'.basename($filename).'"');
            header('Content-Length: ' . filesize($filename));
            header('Pragma: public');

            // Clear system output buffer
            flush();

            // Read the size of the file
            readfile($filename);

            // Terminate from the script
            exit();
        } else {
            $_SESSION['error'] = "The requested material is not available.";
            header("Location: courses_materials.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "File does not exist.";
        header("Location: courses_materials.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Filename is not defined.";
    header("Location: courses_materials.php");
    exit();
}
?>
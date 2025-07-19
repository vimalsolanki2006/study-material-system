<?php

//starting the session
session_start();

// Initializing  error variable
$error = '';

// Including database connection file 
require 'includes/config.php';

//verifing database connection is established or not 
if (!isset($link) || $link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}

//handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    if (empty($_POST['email']) || empty($_POST['password'])) {
        $error = "Please fill in all fields";
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        try {
            // Preparing SQL statement for execution
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $link->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    
                    if (password_verify($password, $user['password'])) {
                        // Setting session variables
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['name'] = $user['name'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];
                        
                        // Redirecting to admin/user dashboard based on role
                        if ($user['role'] == 'admin') {
                            header("Location: admin/dashboard.php");
                        } else {
                            header("Location: user/dashboard.php");
                        }
                        exit();
                    } else {
                        $error = "Invalid email or password";
                    }
                } else {
                    $error = "Invalid email or password";
                }
                $stmt->close();
            } else {
                $error = "Database error. Please try again later.";
            }
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="page-container">
    <section class="auth-form">
        <h2>Login to Your Account</h2>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form action="<?php  echo $_SERVER["PHP_SELF"]; ?>" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <p class="form-footer">Don't have an account? <a href="register.php">Register here</a></p>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
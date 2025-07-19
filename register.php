<?php
include 'includes/config.php';

 // defining Available roles
$roles = ['student', 'admin'];


//handing form data and storing it to the database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE email = ?";
    $stmt = $link->prepare($check_email);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = "Email already registered";
    } else {
        // Insert new user
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $link->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        
        if ($stmt->execute()) {
            $success = "Registration successful! Please login.";
            header("refresh:2; url=login.php");
        } else {
            $error = "Registration failed: " . $link->error;
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<section class="auth-form">
    <h2>Create an Account</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="role">Account Type</label>
            <select id="role" name="role" required>
                <option value="">Select Role</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?php echo $r; ?>"><?php echo ucfirst($r); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn">Register</button>
    </form>
    <p class="form-footer">Already have an account? <a href="login.php">Login here</a></p>
</section>

<?php include 'includes/footer.php'; ?>
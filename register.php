<?php
$success = '';
$error = '';

require_once "configuration.php";
require_once "session.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {

    $fullname = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST["confirm_password"]);
    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Check if email already exists
    if ($stmt = $db->prepare("SELECT id FROM users WHERE email = ?")) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = '<p class="error">The email address is already registered!</p>';
        } else {
            // Validate password
            if (strlen($password) < 6) {
                $error .= '<p class="error">Password must have at least 6 characters.</p>';
            }

            // Validate confirm password
            if ($password !== $confirm_password) {
                $error .= '<p class="error">Password did not match.</p>';
            }

            // If no errors, insert user
            if (empty($error)) {
                if ($insertStmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)")) {
                    $insertStmt->bind_param("sss", $fullname, $email, $password_hash);
                    if ($insertStmt->execute()) {
                        $success = '<p class="success">Your registration was successful!</p>';
                    } else {
                        $error = '<p class="error">Something went wrong! Please try again.</p>';
                    }
                    $insertStmt->close();
                } else {
                    $error = '<p class="error">Database error. Please try again later.</p>';
                }
            }
        }

        $stmt->close();
    } else {
        $error = '<p class="error">Database error. Please try again later.</p>';
    }

    $db->close(); // Close DB connection
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>Register</h2>
            <p>Please fill this form to create an account.</p>

            <!-- Display success or error -->
            <?php echo $success; ?>
            <?php echo $error; ?>

            <form action="" method="post">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>    
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>    
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <input type="submit" name="submit" class="btn btn-primary" value="Submit">
                </div>
                <p>Already have an account? <a href="login.php">Login here</a>.</p>
            </form>
        </div>
    </div>
</div>    
</body>
</html>
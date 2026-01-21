<?php
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION["userid"])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome <?php echo $_SESSION["user"]["name"]; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>
                Hello, <strong><?php echo $_SESSION["user"]["name"]; ?></strong>.
                Welcome to demo site.
            </h1>
        </div>
        <p>
            <a href="logout.php" class="btn btn-secondary btn-lg active" role="button">
                Log Out
            </a>
        </p>
    </div>
</div>
</body>
</html>
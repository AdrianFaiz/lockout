<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit;
}

// Get the logged-in user's username
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home Page</title>
</head>
<body>
    <h2>Welcome, <?php echo $username; ?>!</h2>
    <p>This is your home page.</p>

    <p><a href="lOgout.php">Logout</a></p>
</body>
</html>
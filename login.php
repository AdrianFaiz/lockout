<?php
// Start session
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lockout"; // Updated database name

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define variables and set to empty values
$username = $password = '';
$usernameErr = $passwordErr = '';
$maxFailedAttempts = 3; // Maximum number of allowed failed attempts
$lockoutDuration = 60; // Lockout duration in seconds (1 minute)

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate username
    if (empty($_POST['username'])) {
        $usernameErr = 'Username is required';
    } else {
        $username = $_POST['username'];
    }

    // Validate password
    if (empty($_POST['password'])) {
        $passwordErr = 'Password is required';
    } else {
        $password = $_POST['password'];
    }

    // If there are no errors, proceed with login
    if (empty($usernameErr) && empty($passwordErr)) {
        // Retrieve user information from the database
        $query = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($query);

        // Check if the user exists
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $storedPassword = $row['password'];
            $failedAttempts = $row['failed_attempts'];
            $lockedUntil = $row['locked_until'];

            // Check if the account is locked
            if ($lockedUntil && $lockedUntil > date('Y-m-d H:i:s')) {
                // Account is locked
                $remainingTime = strtotime($lockedUntil) - time();
                echo "Account is locked. Please try again after $remainingTime seconds.";
            } else {
                // Verify the password
                if ($password === $storedPassword) {
                    // Password is correct
                    // Reset failed attempts counter and locked until time
                    $failedAttempts = 0;
                    $lockedUntil = null;

                    // Update the user's failed attempts and locked until time
                    $query = "UPDATE users SET failed_attempts = $failedAttempts, locked_until = '$lockedUntil' WHERE username = '$username'";
                    $conn->query($query);

                    // Store user information in session
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];

                    // Redirect to the user's home page after successful login
                    header("Location: home.php");
                    exit;
                } else {
                    // Password is incorrect
                    $failedAttempts++;

                    // Check if the account should be locked
                    if ($failedAttempts >= $maxFailedAttempts) {
                        $lockedUntil = date('Y-m-d H:i:s', time() + $lockoutDuration);
                    }

                    // Update the user's failed attempts and locked until time
                    $query = "UPDATE users SET failed_attempts = $failedAttempts, locked_until = '$lockedUntil' WHERE username = '$username'";
                    $conn->query($query);

                    echo "Invalid username or password.";
                }
            }
        } else {
            // User does not exist
            echo "Invalid username or password.";
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Form</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Reload the page after 1 minute (60 seconds) if it was previously locked
            var locked = <?php echo ($lockedUntil && $lockedUntil > date('Y-m-d H:i:s')) ? 'true' : 'false'; ?>;
            if (locked) {
                setTimeout(function() {
                    location.reload();
                }, 60000); // 1 minute (60 seconds)
            }
        });
    </script>
</head>
<body>
    <h2>Login Form</h2>
    <form method="post" action="">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>">
        <span style="color: red;"><?php echo isset($usernameErr) ? $usernameErr : ''; ?></span><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password">
        <span style="color: red;"><?php echo isset($passwordErr) ? $passwordErr : ''; ?></span><br>

        <input type="submit" value="Login">
    </form>
</body>
</html>

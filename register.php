<?php
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

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate username
    if (empty($_POST['username'])) {
        $usernameErr = 'Username is required';
    } else {
        $username = $_POST['username'];
        // Check if username already exists in the database
        $query = "SELECT id FROM users WHERE username = '$username'"; // Updated table name
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            $usernameErr = 'Username already taken';
        }
    }

    // Validate password
    if (empty($_POST['password'])) {
        $passwordErr = 'Password is required';
    } else {
        $password = $_POST['password'];
    }

    // If there are no errors, proceed with registration
    if (empty($usernameErr) && empty($passwordErr)) {
        // Insert user information into the database
        $query = "INSERT INTO users (username, password) VALUES ('$username', '$password')"; // Updated table name
        if ($conn->query($query) === TRUE) {
            echo "User registered successfully.";
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration Form</title>
</head>
<body>
    <h2>Registration Form</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>">
        <span style="color: red;"><?php echo isset($usernameErr) ? $usernameErr : ''; ?></span><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password">
        <span style="color: red;"><?php echo isset($passwordErr) ? $passwordErr : ''; ?></span><br>

        <input type="submit" value="Register">
    </form>
</body>
</html>

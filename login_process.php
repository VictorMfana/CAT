<?php
// Include the database connection file
include('db_connection.php');

if(isset($_POST['login'])) {
    // Retrieve user input
    $username = $_POST['username'];
    $password = $_POST['password'];

    // SQL query to check if the user exists in the database
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // User found, redirect to a welcome page or perform other actions
        header('Location: welcome.php');
    } else {
        // User not found, you might want to display an error message
        echo "Invalid username or password";
    }
}

// Close the database connection
$conn->close();
?>

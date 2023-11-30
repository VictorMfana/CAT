<?php
// Include the database connection file
include('db_connection.php');

if(isset($_POST['register'])) {
    // Retrieve user input
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // SQL query to insert the new user into the database
    $sql = "INSERT INTO users (first_name, last_name, username, email, password) VALUES ('$firstName', '$lastName','$username', '$email', '$password')";

    if ($conn->query($sql) === TRUE) {
        // Registration successful, you might want to redirect to a login page
        header('Location: login.php');
    } else {
        // Registration failed, you might want to display an error message
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>

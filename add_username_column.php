<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "your_database_name";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to add username column
$sql = "ALTER TABLE users ADD COLUMN username VARCHAR(255) NOT NULL";

if ($conn->query($sql) === TRUE) {
    echo "Column 'username' added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
?>

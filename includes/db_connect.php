<?php
$servername = "localhost";
$username = "root"; // Apna DB username
$password = ""; // Apna DB password
$dbname = "recruitment_chatbot";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Security ke liye prepared statements use karenge
?>
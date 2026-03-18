<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";  // use your actual password if applicable
$db   = "iscu_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("❌ Connection failed: " . $conn->connect_error);
}

echo "✅ Successfully connected to the database " . $db;
?>

<?php
// config/db.php  –  connexion centrale
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');          // ← ton mot de passe MariaDB
define('DB_NAME', 'iscu_db');

function getDB(): mysqli {
  static $conn = null;
  if ($conn === null) {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
    if ($conn->connect_error) {
      http_response_code(500);
      die(json_encode(['error' => 'DB connection failed']));
    }
  }
  return $conn;
}

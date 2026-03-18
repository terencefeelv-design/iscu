<?php
// api/save_contact.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$name    = trim($data['name']    ?? '');
$email   = trim($data['email']   ?? '');
$message = trim($data['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
  http_response_code(400);
  echo json_encode(['error' => 'Alle Felder sind Pflicht']);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['error' => 'Ungültige E-Mail-Adresse']);
  exit;
}

$db   = getDB();
$stmt = $db->prepare(
  'INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)'
);
$stmt->bind_param('sss', $name, $email, $message);

if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  http_response_code(500);
  echo json_encode(['error' => 'Speichern fehlgeschlagen']);
}
$stmt->close();

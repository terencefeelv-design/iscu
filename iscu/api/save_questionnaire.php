<?php
// api/save_questionnaire.php
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

$role      = trim($data['role']      ?? '');
$goal      = trim($data['goal']      ?? '');
$message   = trim($data['message']   ?? '');
$firstname = trim($data['firstname'] ?? '');
$lastname  = trim($data['lastname']  ?? '');
$email     = trim($data['email']     ?? '');
$phone     = trim($data['phone']     ?? '');

// Validation minimale
if (empty($firstname) || empty($email)) {
  http_response_code(400);
  echo json_encode(['error' => 'Vorname und E-Mail sind Pflichtfelder']);
  exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['error' => 'Ungültige E-Mail-Adresse']);
  exit;
}

$db   = getDB();
$stmt = $db->prepare(
  'INSERT INTO questionnaire_responses (role, goal, message, firstname, lastname, email, phone)
   VALUES (?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param('sssssss', $role, $goal, $message, $firstname, $lastname, $email, $phone);

if ($stmt->execute()) {
  echo json_encode(['success' => true, 'id' => $db->insert_id]);
} else {
  http_response_code(500);
  echo json_encode(['error' => 'Speichern fehlgeschlagen']);
}
$stmt->close();

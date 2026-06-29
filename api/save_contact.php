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

function fail(int $status, string $message): void {
  http_response_code($status);
  echo json_encode(['error' => $message]);
  exit;
}

function cleanText(?string $value): string {
  return trim((string) $value);
}

function normalizeFiles(array $files, string $field): array {
  if (empty($files[$field])) return [];

  $file = $files[$field];
  if (is_array($file['name'])) {
    $normalized = [];
    foreach ($file['name'] as $i => $name) {
      $normalized[] = [
        'name' => $name,
        'type' => $file['type'][$i] ?? '',
        'tmp_name' => $file['tmp_name'][$i] ?? '',
        'error' => $file['error'][$i] ?? UPLOAD_ERR_NO_FILE,
        'size' => $file['size'][$i] ?? 0,
      ];
    }
    return $normalized;
  }

  return [$file];
}

function saveUpload(array $file, string $field, array $allowedExtensions): ?string {
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    fail(400, 'Upload fehlgeschlagen');
  }

  $maxBytes = 5 * 1024 * 1024;
  if (($file['size'] ?? 0) > $maxBytes) {
    fail(400, 'Datei zu gross. Maximal 5 MB pro Datei.');
  }

  $originalName = (string) ($file['name'] ?? '');
  $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
  if (!in_array($extension, $allowedExtensions, true)) {
    fail(400, 'Ungültiger Dateityp. Erlaubt: ' . strtoupper(implode(', ', $allowedExtensions)) . '.');
  }

  $tmpName = (string) ($file['tmp_name'] ?? '');
  if (!is_uploaded_file($tmpName)) {
    fail(400, 'Ungültiger Upload');
  }

  $allowedMimeByExtension = [
    'pdf' => ['application/pdf', 'application/x-pdf'],
    'doc' => ['application/msword', 'application/octet-stream'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
  ];

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime = $finfo->file($tmpName) ?: '';
  if (!in_array($mime, $allowedMimeByExtension[$extension] ?? [], true)) {
    fail(400, 'Ungültiger Dateiinhalt');
  }

  $uploadDir = dirname(__DIR__) . '/files/applications/' . date('Y-m');
  if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    fail(500, 'Upload-Verzeichnis konnte nicht erstellt werden');
  }

  $safeField = preg_replace('/[^a-z0-9_-]+/i', '-', $field);
  $safeBase = preg_replace('/[^a-z0-9._-]+/i', '-', pathinfo($originalName, PATHINFO_FILENAME));
  $filename = $safeField . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '-' . $safeBase . '.' . $extension;
  $target = $uploadDir . '/' . $filename;

  if (!move_uploaded_file($tmpName, $target)) {
    fail(500, 'Datei konnte nicht gespeichert werden');
  }

  return 'files/applications/' . date('Y-m') . '/' . $filename;
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

if (stripos($contentType, 'multipart/form-data') !== false) {
  $firstName = cleanText($_POST['vorname'] ?? '');
  $lastName = cleanText($_POST['nachname'] ?? '');
  $name = cleanText($_POST['name'] ?? trim($firstName . ' ' . $lastName));
  $email = cleanText($_POST['email'] ?? '');
  $phone = cleanText($_POST['phone'] ?? '');
  $position = cleanText($_POST['position'] ?? '');
  $type = cleanText($_POST['type'] ?? 'bewerbung');
  $motivation = cleanText($_POST['motivation'] ?? ($_POST['message'] ?? ''));

  if ($name === '' || $email === '' || $motivation === '') {
    fail(400, 'Name, E-Mail und Nachricht sind Pflicht');
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail(400, 'Ungültige E-Mail-Adresse');
  }

  $savedFiles = [];
  $fileFields = [
    'cv' => ['pdf', 'doc', 'docx'],
    'zeugnisse' => ['pdf', 'doc', 'docx'],
    'documents' => ['pdf'],
  ];

  foreach ($fileFields as $field => $extensions) {
    foreach (normalizeFiles($_FILES, $field) as $file) {
      $path = saveUpload($file, $field, $extensions);
      if ($path !== null) {
        $savedFiles[] = ucfirst($field) . ': ' . $path;
      }
    }
  }

  $messageParts = [
    'Typ: ' . $type,
    $position !== '' ? 'Position: ' . $position : null,
    $phone !== '' ? 'Telefon: ' . $phone : null,
    'Nachricht: ' . $motivation,
  ];
  if (!empty($savedFiles)) {
    $messageParts[] = "Dokumente:\n" . implode("\n", $savedFiles);
  }

  $message = implode("\n\n", array_filter($messageParts));
} else {
  $data = json_decode(file_get_contents('php://input'), true);
  if (!is_array($data)) {
    fail(400, 'Ungültige Anfrage');
  }

  $name = cleanText($data['name'] ?? '');
  $email = cleanText($data['email'] ?? '');
  $message = cleanText($data['message'] ?? '');

  if ($name === '' || $email === '' || $message === '') {
    fail(400, 'Alle Felder sind Pflicht');
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail(400, 'Ungültige E-Mail-Adresse');
  }
}

$db = getDB();
$stmt = $db->prepare(
  'INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)'
);
$stmt->bind_param('sss', $name, $email, $message);

if ($stmt->execute()) {
  echo json_encode(['success' => true]);
} else {
  fail(500, 'Speichern fehlgeschlagen');
}

$stmt->close();

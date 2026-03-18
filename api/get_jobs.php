<?php
// api/get_jobs.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/db.php';

$db     = getDB();
$result = $db->query('SELECT id, title, location, job_type, description FROM jobs ORDER BY posted_at DESC');

$jobs = [];
while ($row = $result->fetch_assoc()) {
  $jobs[] = $row;
}

echo json_encode($jobs);

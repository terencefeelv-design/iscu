<?php
// admin/index.php  –  tableau de bord simplifié (login basique par session)
session_start();
require_once '../config/db.php';

// ── LOGIN ────────────────────────────────────────────────────
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
  $db   = getDB();
  $user = trim($_POST['username'] ?? '');
  $pass = trim($_POST['password'] ?? '');

  $stmt = $db->prepare('SELECT id, password FROM admin_users WHERE username = ?');
  $stmt->bind_param('s', $user);
  $stmt->execute();
  $stmt->bind_result($uid, $hash);
  $stmt->fetch();
  $stmt->close();

  if ($uid && password_verify($pass, $hash)) {
    $_SESSION['admin_id'] = $uid;
    $db->query("UPDATE admin_users SET last_login = NOW() WHERE id = $uid");
    header('Location: index.php');
    exit;
  }
  $error = 'Ungültige Anmeldedaten.';
}

if (isset($_POST['logout'])) {
  session_destroy();
  header('Location: index.php');
  exit;
}

$loggedIn = !empty($_SESSION['admin_id']);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>iscu Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    body { padding: 40px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #e0e0e0; padding: 10px 14px; text-align: left; font-size: .9rem; }
    th { background: #f5f5f7; font-weight: 600; }
    tr:hover { background: #fafafa; }
    h2 { margin-top: 40px; }
    .tabs { display: flex; gap: 16px; margin-top: 30px; }
    .tab-btn { padding: 10px 22px; border-radius: 8px; border: 1px solid #d2d2d7;
               cursor: pointer; background: white; font-weight: 500; }
    .tab-btn.active { background: #1d1d1f; color: #f5f5f7; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .login-box { max-width: 360px; margin: 100px auto; background: white;
                 border-radius: 20px; padding: 50px; box-shadow: 0 10px 30px rgba(0,0,0,.07); }
    .login-box input { display: block; width: 100%; margin-bottom: 14px;
                       padding: 12px; border: 1px solid #d2d2d7; border-radius: 10px; font-size: 1rem; }
    .login-box button { width: 100%; }
    .error { color: red; margin-bottom: 12px; font-size: .9rem; }
  </style>
</head>
<body>

<?php if (!$loggedIn): ?>
  <div class="login-box">
    <h2 style="text-align:center;margin-bottom:30px">Admin Login</h2>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST">
      <input type="text"     name="username" placeholder="Benutzername" required>
      <input type="password" name="password" placeholder="Passwort"     required>
      <button type="submit" name="login">Anmelden</button>
    </form>
  </div>

<?php else: ?>

  <div style="display:flex;justify-content:space-between;align-items:center">
    <div class="brand" style="font-size:22px;font-weight:600">iscu<span style="color:#a1a1a6">.ch</span> — Admin</div>
    <form method="POST"><button name="logout" style="padding:8px 18px">Abmelden</button></form>
  </div>

  <div class="tabs">
    <button class="tab-btn active" onclick="show('questionnaire',this)">Fragebogen</button>
    <button class="tab-btn"        onclick="show('contacts',this)">Kontaktanfragen</button>
    <button class="tab-btn"        onclick="show('jobs',this)">Jobs</button>
  </div>

  <!-- QUESTIONNAIRE -->
  <div id="questionnaire" class="tab-content active">
    <h2>Fragebogen-Antworten</h2>
    <table>
      <tr><th>#</th><th>Rolle</th><th>Ziel</th><th>Nachricht</th><th>Vorname</th><th>Nachname</th><th>E-Mail</th><th>Telefon</th><th>Datum</th></tr>
      <?php
        $rows = getDB()->query('SELECT * FROM questionnaire_responses ORDER BY created_at DESC');
        while ($r = $rows->fetch_assoc()):
      ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['role']) ?></td>
        <td><?= htmlspecialchars($r['goal']) ?></td>
        <td><?= htmlspecialchars(mb_strimwidth($r['message'], 0, 60, '…')) ?></td>
        <td><?= htmlspecialchars($r['firstname']) ?></td>
        <td><?= htmlspecialchars($r['lastname']) ?></td>
        <td><?= htmlspecialchars($r['email']) ?></td>
        <td><?= htmlspecialchars($r['phone']) ?></td>
        <td><?= $r['created_at'] ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>

  <!-- CONTACTS -->
  <div id="contacts" class="tab-content">
    <h2>Kontaktanfragen</h2>
    <table>
      <tr><th>#</th><th>Name</th><th>E-Mail</th><th>Nachricht</th><th>Datum</th></tr>
      <?php
        $rows = getDB()->query('SELECT * FROM contact_messages ORDER BY created_at DESC');
        while ($r = $rows->fetch_assoc()):
      ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['name']) ?></td>
        <td><?= htmlspecialchars($r['email']) ?></td>
        <td><?= htmlspecialchars(mb_strimwidth($r['message'], 0, 80, '…')) ?></td>
        <td><?= $r['created_at'] ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>

  <!-- JOBS -->
  <div id="jobs" class="tab-content">
    <h2>Stellenangebote</h2>
    <table>
      <tr><th>#</th><th>Titel</th><th>Ort</th><th>Typ</th><th>Beschreibung</th><th>Datum</th></tr>
      <?php
        $rows = getDB()->query('SELECT * FROM jobs ORDER BY posted_at DESC');
        while ($r = $rows->fetch_assoc()):
      ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['title']) ?></td>
        <td><?= htmlspecialchars($r['location']) ?></td>
        <td><?= htmlspecialchars($r['job_type']) ?></td>
        <td><?= htmlspecialchars(mb_strimwidth($r['description'], 0, 80, '…')) ?></td>
        <td><?= $r['posted_at'] ?></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </div>

  <script>
    function show(id, btn) {
      document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
      document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
      document.getElementById(id).classList.add('active');
      btn.classList.add('active');
    }
  </script>

<?php endif; ?>
</body>
</html>

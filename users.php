<?php
// users.php
$activePage = 'users';
require 'includes/db.php';

$flash = '';

// ── HANDLE ADD USER ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = $conn->real_escape_string(trim($_POST['full_name']));
    $age  = (int)$_POST['age'];
    $grp  = $conn->real_escape_string($_POST['age_group']);
    $occ  = $conn->real_escape_string($_POST['occupation']);

    if ($name && $age) {
        // SQL INSERT
        $sql = "INSERT INTO users (full_name, age, age_group, occupation)
                VALUES ('$name', $age, '$grp', '$occ')";
        if ($conn->query($sql)) {
            $flash = "success|👤 User '$name' added! (ID: " . $conn->insert_id . ")";
        } else {
            $flash = "error|❌ " . $conn->error;
        }
    } else {
        $flash = "error|⚠️ Name and Age are required.";
    }
}

// ── HANDLE DELETE ───────────────────────────────────────
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM users WHERE user_id = $del_id");
    $flash = "success|🗑️ User deleted.";
}

// ── SQL SELECT: All users with total hours ──────────────
$usersResult = $conn->query("
    SELECT u.user_id, u.full_name, u.age, u.age_group, u.occupation,
           ROUND(COALESCE(SUM(r.hours_used),0),1) AS total_hrs,
           COUNT(DISTINCT r.record_id) AS records
    FROM users u
    LEFT JOIN usage_records r ON u.user_id = r.user_id
    GROUP BY u.user_id, u.full_name, u.age, u.age_group, u.occupation
    ORDER BY total_hrs DESC
");
$users = [];
while ($row = $usersResult->fetch_assoc()) $users[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Users — Digital Addiction Tracker</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">
  <?php require 'includes/sidebar.php'; ?>
  <main class="main">
    <div class="page-title">👤 User Management</div>
    <div class="page-sub">Add and manage users tracked in the database.</div>

    <?php if ($flash): [$type,$msg] = explode('|',$flash,2); ?>
      <div class="flash <?= $type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <!-- ADD USER FORM -->
    <div class="card-box">
      <h3>➕ Add New User
        <small style="font-weight:400;color:var(--text2);font-size:.72rem">(SQL: INSERT INTO users)</small>
      </h3>
      <form method="POST" action="users.php">
        <div class="form-grid">
          <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="full_name" placeholder="e.g. Tanishq Gupta" required>
          </div>
          <div class="form-group">
            <label>Age *</label>
            <input type="number" name="age" placeholder="e.g. 20" min="5" max="80" required>
          </div>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label>Age Group</label>
            <select name="age_group">
              <option>Teen (13-17)</option>
              <option selected>Young Adult (18-25)</option>
              <option>Adult (26-40)</option>
              <option>Middle-Aged (41-60)</option>
              <option>Senior (60+)</option>
            </select>
          </div>
          <div class="form-group">
            <label>Occupation</label>
            <select name="occupation">
              <option>Student</option>
              <option>Software Engineer</option>
              <option>Teacher</option>
              <option>Business Person</option>
              <option>Doctor</option>
              <option>Homemaker</option>
              <option>Other</option>
            </select>
          </div>
        </div>
        <button type="submit" name="add_user" class="btn">➕ Add User</button>
      </form>
    </div>

    <!-- USERS TABLE -->
    <div class="card-box">
      <h3>📋 Registered Users
        <small style="font-weight:400;color:var(--text2);font-size:.72rem">(SQL: SELECT with JOIN + SUM)</small>
      </h3>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#ID</th>
              <th>Full Name</th>
              <th>Age</th>
              <th>Age Group</th>
              <th>Occupation</th>
              <th>Total Hours</th>
              <th>Records</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $i => $u): ?>
              <tr>
                <td><span class="badge info"><?= $u['user_id'] ?></span></td>
                <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                <td><?= $u['age'] ?></td>
                <td><?= htmlspecialchars($u['age_group']) ?></td>
                <td><?= htmlspecialchars($u['occupation']) ?></td>
                <td><strong><?= $u['total_hrs'] ?>h</strong></td>
                <td><?= $u['records'] ?></td>
                <td>
                  <a href="users.php?delete=<?= $u['user_id'] ?>"
                     class="btn red sm"
                     onclick="return confirm('Delete <?= htmlspecialchars($u['full_name']) ?>?')">
                    🗑️ Delete
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>
</body>
</html>

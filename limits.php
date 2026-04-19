<?php
// limits.php
$activePage = 'limits';
require 'includes/db.php';

$flash = '';
$selectedUser = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;

// ── HANDLE SAVE LIMITS ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_limits'])) {
    $uid = (int)$_POST['user_id'];
    foreach ($_POST['limit'] as $cat_id => $val) {
        $cat_id = (int)$cat_id;
        $limit  = round((float)$val, 2);
        // SQL: INSERT or UPDATE using ON DUPLICATE KEY UPDATE
        $sql = "INSERT INTO usage_limits (user_id, cat_id, daily_limit)
                VALUES ($uid, $cat_id, $limit)
                ON DUPLICATE KEY UPDATE daily_limit = $limit";
        $conn->query($sql);
    }
    $flash = "success|💾 Limits saved for user!";
    $selectedUser = $uid;
}

// ── FETCH USERS ──────────────────────────────────────────
$users = [];
$res = $conn->query("SELECT user_id, full_name FROM users ORDER BY full_name");
while ($r = $res->fetch_assoc()) $users[] = $r;

// ── FETCH CATEGORIES + CURRENT LIMITS + TODAY USAGE ─────
$catData = [];
if ($selectedUser) {
    $res2 = $conn->query("
        SELECT c.cat_id, c.cat_name, c.icon,
               COALESCE(l.daily_limit, 0) AS daily_limit,
               ROUND(COALESCE(SUM(CASE WHEN r.usage_date=CURDATE() THEN r.hours_used ELSE 0 END),0),1) AS used_today
        FROM categories c
        LEFT JOIN usage_limits l    ON c.cat_id = l.cat_id AND l.user_id = $selectedUser
        LEFT JOIN usage_records r   ON c.cat_id = r.cat_id AND r.user_id = $selectedUser
        GROUP BY c.cat_id, c.cat_name, c.icon, l.daily_limit
        ORDER BY c.cat_id
    ");
    while ($r = $res2->fetch_assoc()) $catData[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Set Limits — Digital Addiction Tracker</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">
  <?php require 'includes/sidebar.php'; ?>
  <main class="main">
    <div class="page-title">🚫 Set Daily Limits</div>
    <div class="page-sub">Set how many hours per day a user can use each category.</div>

    <?php if ($flash): [$type,$msg] = explode('|',$flash,2); ?>
      <div class="flash <?= $type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <!-- SELECT USER -->
    <div class="card-box">
      <h3>👤 Select User</h3>
      <form method="GET" action="limits.php">
        <div style="display:flex;gap:12px;align-items:flex-end">
          <div class="form-group" style="flex:1;margin:0">
            <label>Choose User</label>
            <select name="uid" required>
              <option value="">-- Select a user --</option>
              <?php foreach ($users as $u): ?>
                <option value="<?= $u['user_id'] ?>" <?= $selectedUser == $u['user_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($u['full_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn outline">Load Limits</button>
        </div>
      </form>
    </div>

    <!-- LIMIT EDITOR -->
    <?php if ($selectedUser && !empty($catData)): ?>
    <form method="POST" action="limits.php">
      <input type="hidden" name="user_id" value="<?= $selectedUser ?>">
      <div class="card-box">
        <h3>⚙️ Daily Limits per Category
          <small style="font-weight:400;color:var(--text2);font-size:.72rem">
            (SQL: INSERT ... ON DUPLICATE KEY UPDATE)
          </small>
        </h3>
        <p style="font-size:.73rem;color:var(--text2);margin-bottom:12px">
          Enter 0 to disable limit. Changes save to <code>usage_limits</code> table.
        </p>
        <?php foreach ($catData as $cat):
          $lim   = (float)$cat['daily_limit'];
          $used  = (float)$cat['used_today'];
          $over  = $lim > 0 && $used >= $lim;
          $near  = $lim > 0 && $used >= $lim * 0.8 && !$over;
          $pct   = $lim > 0 ? min(100, round($used / $lim * 100)) : 0;
        ?>
          <div class="limit-row">
            <span class="lr-ico"><?= $cat['icon'] ?></span>
            <span class="lr-name"><?= htmlspecialchars($cat['cat_name']) ?></span>
            <input type="number" name="limit[<?= $cat['cat_id'] ?>]"
                   value="<?= $lim ?>" min="0" max="24" step="0.5">
            <span class="lr-unit">h/day</span>
            <?php if ($lim > 0): ?>
              <span class="lr-stat" style="color:<?= $over?'#ef4444':($near?'#f59e0b':'#10b981') ?>">
                <?= $used ?>h used
              </span>
              <span class="badge <?= $over?'danger':($near?'warn':'ok') ?>">
                <?= $over?'OVER':($near?'NEAR':'OK') ?>
              </span>
            <?php else: ?>
              <span class="lr-stat" style="color:var(--text2)">No limit</span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <div style="margin-top:14px">
          <button type="submit" name="save_limits" class="btn green">💾 Save All Limits</button>
        </div>
      </div>
    </form>
    <?php elseif ($selectedUser): ?>
      <div class="alert warn">⚠️ No categories found.</div>
    <?php else: ?>
      <div class="alert info">👆 Please select a user above to view and edit their limits.</div>
    <?php endif; ?>

  </main>
</div>
</body>
</html>

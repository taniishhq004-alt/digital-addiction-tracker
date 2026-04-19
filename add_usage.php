<?php
// add_usage.php
$activePage = 'add';
require 'includes/db.php';

$flash = '';

// ── HANDLE FORM SUBMIT ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id  = (int)$_POST['user_id'];
    $cat_id   = (int)$_POST['cat_id'];
    $app_name = $conn->real_escape_string(trim($_POST['app_name']));
    $date     = $conn->real_escape_string($_POST['usage_date']);
    $hrs      = (float)$_POST['hours'];
    $min      = (float)$_POST['minutes'];
    $note     = $conn->real_escape_string(trim($_POST['note']));
    $total    = round($hrs + ($min / 60), 2);

    if ($user_id && $cat_id && $total > 0 && $date) {
        // SQL INSERT QUERY
        $sql = "INSERT INTO usage_records
                    (user_id, cat_id, app_name, usage_date, hours_used, note)
                VALUES
                    ($user_id, $cat_id, '$app_name', '$date', $total, '$note')";

        if ($conn->query($sql)) {
            $flash = "success|✅ Usage record saved to database! (Record ID: " . $conn->insert_id . ")";
        } else {
            $flash = "error|❌ Error: " . $conn->error;
        }
    } else {
        $flash = "error|⚠️ Please fill all required fields and enter a time greater than 0.";
    }
}

// ── FETCH USERS ──────────────────────────────────────────
$users = [];
$res = $conn->query("SELECT user_id, full_name FROM users ORDER BY full_name");
while ($r = $res->fetch_assoc()) $users[] = $r;

// ── FETCH CATEGORIES ─────────────────────────────────────
$categories = [];
$res2 = $conn->query("SELECT cat_id, cat_name, icon FROM categories ORDER BY cat_id");
while ($r = $res2->fetch_assoc()) $categories[] = $r;

// ── TODAY'S USAGE PER CATEGORY (for showing on cards) ────
$todayUsage = [];
$res3 = $conn->query("
    SELECT cat_id, ROUND(SUM(hours_used),1) AS hrs
    FROM usage_records
    WHERE usage_date = CURDATE()
    GROUP BY cat_id
");
while ($r = $res3->fetch_assoc()) $todayUsage[$r['cat_id']] = $r['hrs'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Usage — Digital Addiction Tracker</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">
  <?php require 'includes/sidebar.php'; ?>
  <main class="main">
    <div class="page-title">➕ Add Screen Time Usage</div>
    <div class="page-sub">Manually enter how much time you spent on a category today.</div>

    <!-- Flash Message -->
    <?php if ($flash): [$type,$msg] = explode('|',$flash,2); ?>
      <div class="flash <?= $type ?>"><?= $msg ?></div>
    <?php endif; ?>

    <!-- ── SQL INSERT FORM ── -->
    <form method="POST" action="add_usage.php">

      <!-- Step 1: User -->
      <div class="card-box">
        <h3>👤 Step 1 — Select User</h3>
        <div class="form-group">
          <label>Choose User *</label>
          <select name="user_id" required>
            <option value="">-- Select a user --</option>
            <?php foreach ($users as $u): ?>
              <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Step 2: Category -->
      <div class="card-box">
        <h3>📱 Step 2 — Select Category</h3>
        <div class="cat-grid">
          <?php foreach ($categories as $i => $cat): ?>
            <label style="cursor:pointer">
              <input type="radio" name="cat_id" value="<?= $cat['cat_id'] ?>"
                     style="display:none"
                     onchange="document.querySelectorAll('.cat-card').forEach(c=>c.classList.remove('selected'));
                               this.closest('.cat-card').classList.add('selected')">
              <div class="cat-card">
                <div class="c-ico"><?= $cat['icon'] ?></div>
                <div class="c-nm"><?= htmlspecialchars($cat['cat_name']) ?></div>
                <?php if (isset($todayUsage[$cat['cat_id']])): ?>
                  <div class="c-hrs"><?= $todayUsage[$cat['cat_id']] ?>h today</div>
                <?php endif; ?>
              </div>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Step 3: Time entry -->
      <div class="card-box">
        <h3>⏱️ Step 3 — Enter Screen Time</h3>
        <div class="form-grid3">
          <div class="form-group">
            <label>Date *</label>
            <input type="date" name="usage_date" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="form-group">
            <label>Hours *</label>
            <input type="number" name="hours" min="0" max="24" step="1" placeholder="e.g. 2">
          </div>
          <div class="form-group">
            <label>Minutes</label>
            <input type="number" name="minutes" min="0" max="59" step="5" placeholder="e.g. 30">
          </div>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label>App / Website Name</label>
            <input type="text" name="app_name" placeholder="e.g. Instagram, YouTube, BGMI...">
          </div>
          <div class="form-group">
            <label>Note (optional)</label>
            <input type="text" name="note" placeholder="e.g. Binge watched a web series">
          </div>
        </div>

        <div class="alert info" style="margin-bottom:14px">
          💡 This runs: <code style="background:rgba(0,0,0,0.3);padding:2px 6px;border-radius:4px;font-size:.74rem">
          INSERT INTO usage_records (user_id, cat_id, app_name, usage_date, hours_used, note) VALUES (...)
          </code>
        </div>

        <button type="submit" class="btn green">💾 Save to Database</button>
      </div>

    </form>
  </main>
</div>
<script>
// Highlight selected category card
document.querySelectorAll('input[name="cat_id"]').forEach(radio => {
  radio.addEventListener('change', () => {
    document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('selected'));
    radio.closest('label').querySelector('.cat-card').classList.add('selected');
  });
});
</script>
</body>
</html>

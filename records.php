<?php
// records.php
$activePage = 'records';
require 'includes/db.php';

// ── HANDLE DELETE ───────────────────────────────────────
if (isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $conn->query("DELETE FROM usage_records WHERE record_id = $did");
    header("Location: records.php");
    exit;
}

// ── FILTERS ─────────────────────────────────────────────
$filterUser = isset($_GET['uid'])  ? (int)$_GET['uid'] : 0;
$filterCat  = isset($_GET['cat'])  ? (int)$_GET['cat'] : 0;
$filterDate = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : '';

$where = "WHERE 1=1";
if ($filterUser) $where .= " AND r.user_id = $filterUser";
if ($filterCat)  $where .= " AND r.cat_id  = $filterCat";
if ($filterDate) $where .= " AND r.usage_date = '$filterDate'";

// ── SQL SELECT with JOINs ───────────────────────────────
$result = $conn->query("
    SELECT r.record_id, r.usage_date, r.hours_used, r.app_name, r.note,
           u.full_name, c.cat_name, c.icon
    FROM usage_records r
    JOIN users      u ON r.user_id = u.user_id
    JOIN categories c ON r.cat_id  = c.cat_id
    $where
    ORDER BY r.usage_date DESC, r.record_id DESC
");
$records = [];
while ($row = $result->fetch_assoc()) $records[] = $row;

// ── TOTALS ───────────────────────────────────────────────
$totalHrs = array_sum(array_column($records, 'hours_used'));

// ── DROPDOWN DATA ────────────────────────────────────────
$users = [];
$res = $conn->query("SELECT user_id, full_name FROM users ORDER BY full_name");
while ($r = $res->fetch_assoc()) $users[] = $r;

$cats = [];
$res2 = $conn->query("SELECT cat_id, cat_name, icon FROM categories ORDER BY cat_id");
while ($r = $res2->fetch_assoc()) $cats[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Records — Digital Addiction Tracker</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">
  <?php require 'includes/sidebar.php'; ?>
  <main class="main">
    <div class="page-title">📋 All Usage Records</div>
    <div class="page-sub">Every record stored in the <code>usage_records</code> table with JOIN queries.</div>

    <!-- FILTER FORM -->
    <div class="card-box">
      <h3>🔍 Filter Records
        <small style="font-weight:400;color:var(--text2);font-size:.72rem">
          (SQL: SELECT ... WHERE + JOIN)
        </small>
      </h3>
      <form method="GET" action="records.php">
        <div class="form-grid3">
          <div class="form-group">
            <label>Filter by User</label>
            <select name="uid">
              <option value="">All Users</option>
              <?php foreach ($users as $u): ?>
                <option value="<?= $u['user_id'] ?>" <?= $filterUser==$u['user_id']?'selected':'' ?>>
                  <?= htmlspecialchars($u['full_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Filter by Category</label>
            <select name="cat">
              <option value="">All Categories</option>
              <?php foreach ($cats as $c): ?>
                <option value="<?= $c['cat_id'] ?>" <?= $filterCat==$c['cat_id']?'selected':'' ?>>
                  <?= $c['icon'] ?> <?= htmlspecialchars($c['cat_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Filter by Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>">
          </div>
        </div>
        <div style="display:flex;gap:10px">
          <button type="submit" class="btn">🔍 Filter</button>
          <a href="records.php" class="btn outline">✖ Clear</a>
        </div>
      </form>
    </div>

    <!-- SUMMARY -->
    <div class="stats-row" style="grid-template-columns:repeat(3,1fr);margin-bottom:14px">
      <div class="stat"><div class="s-val"><?= count($records) ?></div><div class="s-lbl">Records Found</div></div>
      <div class="stat"><div class="s-val"><?= round($totalHrs,1) ?>h</div><div class="s-lbl">Total Hours</div></div>
      <div class="stat"><div class="s-val"><?= count($records)?round($totalHrs/count($records),1):0 ?>h</div><div class="s-lbl">Avg per Record</div></div>
    </div>

    <!-- RECORDS TABLE -->
    <div class="card-box">
      <h3>🗄️ Database Records (<?= count($records) ?> rows)</h3>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>User</th>
              <th>Date</th>
              <th>Category</th>
              <th>App / Site</th>
              <th>Hours</th>
              <th>Note</th>
              <th>Del</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($records)): ?>
              <tr><td colspan="8" style="text-align:center;color:var(--text2);padding:20px">No records found</td></tr>
            <?php else: foreach ($records as $rec): ?>
              <tr>
                <td><span class="badge info"><?= $rec['record_id'] ?></span></td>
                <td><strong><?= htmlspecialchars($rec['full_name']) ?></strong></td>
                <td><?= $rec['usage_date'] ?></td>
                <td><?= $rec['icon'] ?> <?= htmlspecialchars($rec['cat_name']) ?></td>
                <td><?= htmlspecialchars($rec['app_name'] ?: '-') ?></td>
                <td><strong><?= $rec['hours_used'] ?>h</strong></td>
                <td style="color:var(--text2)"><?= htmlspecialchars($rec['note'] ?: '-') ?></td>
                <td>
                  <a href="records.php?delete=<?= $rec['record_id'] ?>"
                     class="btn red sm"
                     onclick="return confirm('Delete this record?')">🗑️</a>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>
</body>
</html>

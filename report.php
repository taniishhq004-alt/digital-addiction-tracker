<?php
// report.php
$activePage = 'report';
require 'includes/db.php';

$selectedUser = isset($_GET['uid'])  ? (int)$_GET['uid']  : 0;
$fromDate     = isset($_GET['from']) ? $_GET['from'] : date('Y-m-d', strtotime('-7 days'));
$toDate       = isset($_GET['to'])   ? $_GET['to']   : date('Y-m-d');

$users = [];
$res = $conn->query("SELECT user_id, full_name FROM users ORDER BY full_name");
while ($r = $res->fetch_assoc()) $users[] = $r;

// ── REPORT DATA (only when user selects options) ─────────
$reportData = null;
if (isset($_GET['generate'])) {
    $from = $conn->real_escape_string($fromDate);
    $to   = $conn->real_escape_string($toDate);
    $uwhere = $selectedUser ? "AND r.user_id = $selectedUser" : "";

    // User info
    $userInfo = $selectedUser ?
        $conn->query("SELECT * FROM users WHERE user_id = $selectedUser")->fetch_assoc() :
        ['full_name' => 'All Users'];

    // Total hours
    $totRow = $conn->query("
        SELECT ROUND(SUM(hours_used),1) AS t, COUNT(*) AS cnt
        FROM usage_records r
        WHERE r.usage_date BETWEEN '$from' AND '$to' $uwhere
    ")->fetch_assoc();

    // Category breakdown
    $catRows = [];
    $catRes = $conn->query("
        SELECT c.icon, c.cat_name, ROUND(SUM(r.hours_used),1) AS hrs
        FROM usage_records r
        JOIN categories c ON r.cat_id = c.cat_id
        WHERE r.usage_date BETWEEN '$from' AND '$to' $uwhere
        GROUP BY c.cat_id, c.cat_name, c.icon
        ORDER BY hrs DESC
    ");
    while ($row = $catRes->fetch_assoc()) $catRows[] = $row;

    // Daily breakdown
    $dailyRows = [];
    $dayRes = $conn->query("
        SELECT r.usage_date, ROUND(SUM(r.hours_used),1) AS hrs
        FROM usage_records r
        WHERE r.usage_date BETWEEN '$from' AND '$to' $uwhere
        GROUP BY r.usage_date
        ORDER BY r.usage_date DESC
    ");
    while ($row = $dayRes->fetch_assoc()) $dailyRows[] = $row;

    // Violations
    $violations = [];
    $vRes = $conn->query("
        SELECT u.full_name, c.cat_name, c.icon,
               ROUND(SUM(r.hours_used),1) AS used_day,
               l.daily_limit, r.usage_date
        FROM usage_records r
        JOIN users u ON r.user_id = u.user_id
        JOIN categories c ON r.cat_id = c.cat_id
        JOIN usage_limits l ON r.user_id = l.user_id AND r.cat_id = l.cat_id
        WHERE r.usage_date BETWEEN '$from' AND '$to'
          AND l.daily_limit > 0 $uwhere
        GROUP BY r.user_id, r.cat_id, r.usage_date, l.daily_limit, u.full_name, c.cat_name, c.icon
        HAVING ROUND(SUM(r.hours_used),1) >= l.daily_limit
        ORDER BY r.usage_date DESC
    ");
    while ($row = $vRes->fetch_assoc()) $violations[] = $row;

    $reportData = compact('userInfo','totRow','catRows','dailyRows','violations');
}

// ── PRINT / PDF MODE ─────────────────────────────────────
$printMode = isset($_GET['print']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PDF Report — Digital Addiction Tracker</title>
  <?php if (!$printMode): ?>
  <link rel="stylesheet" href="css/style.css">
  <?php else: ?>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap');
    body{font-family:'Nunito',sans-serif;color:#1a1a2e;background:#fff;padding:24px;max-width:800px;margin:auto}
    .rpt-header{background:linear-gradient(135deg,#5b5ef4,#8b5cf6);color:#fff;padding:24px;border-radius:12px;margin-bottom:20px}
    .rpt-header h1{font-size:1.4rem;font-weight:900}
    .rpt-header p{font-size:.78rem;opacity:.85;margin-top:4px}
    h2{font-size:1rem;font-weight:900;border-bottom:2px solid #5b5ef4;padding-bottom:5px;margin:16px 0 8px;color:#1a1a2e}
    table{width:100%;border-collapse:collapse;font-size:.8rem;margin-bottom:12px}
    th{background:#f0f4ff;padding:7px 10px;text-align:left;font-weight:700}
    td{padding:7px 10px;border-bottom:1px solid #e5e7eb}
    .danger{color:#b91c1c;font-weight:700}
    .badge{display:inline-block;padding:2px 8px;border-radius:20px;font-size:.65rem;font-weight:700}
    .danger-badge{background:#fee2e2;color:#b91c1c}
    .footer{margin-top:24px;font-size:.7rem;color:#6b7280;border-top:1px solid #e5e7eb;padding-top:12px}
    @media print{body{padding:0} button{display:none}}
  </style>
  <?php endif; ?>
</head>
<body>
<?php if (!$printMode): ?>
<div class="layout">
  <?php require 'includes/sidebar.php'; ?>
  <main class="main">
    <div class="page-title">📄 Generate PDF Report</div>
    <div class="page-sub">Select options and generate a printable / PDF report.</div>

    <!-- REPORT OPTIONS -->
    <div class="card-box">
      <h3>⚙️ Report Settings</h3>
      <form method="GET" action="report.php">
        <div class="form-grid">
          <div class="form-group">
            <label>Select User</label>
            <select name="uid">
              <option value="">All Users</option>
              <?php foreach ($users as $u): ?>
                <option value="<?= $u['user_id'] ?>" <?= $selectedUser==$u['user_id']?'selected':'' ?>>
                  <?= htmlspecialchars($u['full_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="display:none"><input name="generate" value="1"></div>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label>From Date</label>
            <input type="date" name="from" value="<?= htmlspecialchars($fromDate) ?>">
          </div>
          <div class="form-group">
            <label>To Date</label>
            <input type="date" name="to" value="<?= htmlspecialchars($toDate) ?>">
          </div>
        </div>
        <div class="alert info" style="margin-bottom:14px">
          📝 Report includes: User Info · Category breakdown · Daily usage · Limit violations
        </div>
        <div style="display:flex;gap:10px">
          <button type="submit" name="generate" value="1" class="btn">📊 Generate Report</button>
        </div>
      </form>
    </div>

    <?php if ($reportData): ?>
    <!-- PREVIEW -->
    <div class="card-box">
      <h3>👁️ Report Preview
        <a href="?uid=<?=$selectedUser?>&from=<?=$fromDate?>&to=<?=$toDate?>&generate=1&print=1"
           target="_blank" class="btn sm" style="margin-left:auto">🖨️ Open Printable / PDF</a>
      </h3>
      <div class="report-preview">
        <h2>Digital Addiction Tracking Database — Report</h2>
        <p style="font-size:.73rem;color:var(--text2)">Period: <?= $fromDate ?> to <?= $toDate ?> &nbsp;|&nbsp; User: <?= htmlspecialchars($reportData['userInfo']['full_name']) ?></p>

        <h3>Summary</h3>
        <div class="rep-row"><span>Total Screen Time</span><strong><?= $reportData['totRow']['t'] ?>h</strong></div>
        <div class="rep-row"><span>Total Records</span><strong><?= $reportData['totRow']['cnt'] ?></strong></div>
        <div class="rep-row"><span>Limit Violations</span><strong><?= count($reportData['violations']) ?></strong></div>

        <h3>Category Breakdown</h3>
        <?php $totalHrs = $reportData['totRow']['t'] ?: 1; ?>
        <?php foreach ($reportData['catRows'] as $cat): ?>
          <div class="rep-row">
            <span><?= $cat['icon'] ?> <?= htmlspecialchars($cat['cat_name']) ?></span>
            <strong><?= $cat['hrs'] ?>h (<?= round($cat['hrs']/$totalHrs*100,1) ?>%)</strong>
          </div>
        <?php endforeach; ?>

        <?php if (!empty($reportData['violations'])): ?>
        <h3 style="color:#ef4444">⚠️ Limit Violations (<?= count($reportData['violations']) ?>)</h3>
        <?php foreach ($reportData['violations'] as $v): ?>
          <div class="rep-row" style="color:#ef4444">
            <span><?= $v['icon'] ?> <?= htmlspecialchars($v['full_name']) ?> · <?= htmlspecialchars($v['cat_name']) ?> on <?= $v['usage_date'] ?></span>
            <strong><?= $v['used_day'] ?>h / <?= $v['daily_limit'] ?>h limit</strong>
          </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

  </main>
</div>

<?php else: // ── PRINT MODE ──────────────────────────────
  if ($reportData): ?>

<button onclick="window.print()" style="background:#5b5ef4;color:#fff;border:none;padding:10px 24px;border-radius:8px;font-size:.85rem;font-weight:700;cursor:pointer;margin-bottom:16px">
  🖨️ Print / Save as PDF
</button>

<div class="rpt-header">
  <h1>📱 Digital Addiction Tracking Database</h1>
  <p>Jaypee Institute of Information Technology &nbsp;·&nbsp; BCA 2nd Sem DBMS Minor Project &nbsp;·&nbsp; Session 2025-26</p>
  <p>User: <strong><?= htmlspecialchars($reportData['userInfo']['full_name']) ?></strong>
     &nbsp;·&nbsp; Period: <strong><?= $fromDate ?> to <?= $toDate ?></strong>
     &nbsp;·&nbsp; Generated: <strong><?= date('d M Y, h:i A') ?></strong>
  </p>
</div>

<h2>📊 Summary</h2>
<table>
  <tr><th>Metric</th><th>Value</th></tr>
  <tr><td>Total Screen Time</td><td><strong><?= $reportData['totRow']['t'] ?>h</strong></td></tr>
  <tr><td>Total Records</td><td><?= $reportData['totRow']['cnt'] ?></td></tr>
  <tr><td>Report Period</td><td><?= $fromDate ?> to <?= $toDate ?></td></tr>
  <tr><td>Limit Violations</td><td class="danger"><?= count($reportData['violations']) ?></td></tr>
</table>

<h2>📱 Category-wise Breakdown</h2>
<table>
  <thead><tr><th>#</th><th>Category</th><th>Total Hours</th><th>% of Total</th></tr></thead>
  <tbody>
    <?php $total = $reportData['totRow']['t'] ?: 1; $i=1;
    foreach ($reportData['catRows'] as $cat): ?>
      <tr>
        <td><?= $i++ ?></td>
        <td><?= $cat['icon'] ?> <?= htmlspecialchars($cat['cat_name']) ?></td>
        <td><strong><?= $cat['hrs'] ?>h</strong></td>
        <td><?= round($cat['hrs']/$total*100,1) ?>%</td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h2>📅 Daily Usage</h2>
<table>
  <thead><tr><th>Date</th><th>Total Hours</th></tr></thead>
  <tbody>
    <?php foreach ($reportData['dailyRows'] as $day): ?>
      <tr><td><?= $day['usage_date'] ?></td><td><strong><?= $day['hrs'] ?>h</strong></td></tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php if (!empty($reportData['violations'])): ?>
<h2 class="danger">⚠️ Limit Violations</h2>
<table>
  <thead><tr><th>User</th><th>Date</th><th>Category</th><th>Used</th><th>Limit</th><th>Status</th></tr></thead>
  <tbody>
    <?php foreach ($reportData['violations'] as $v): ?>
      <tr>
        <td><?= htmlspecialchars($v['full_name']) ?></td>
        <td><?= $v['usage_date'] ?></td>
        <td><?= $v['icon'] ?> <?= htmlspecialchars($v['cat_name']) ?></td>
        <td class="danger"><?= $v['used_day'] ?>h</td>
        <td><?= $v['daily_limit'] ?>h</td>
        <td><span class="badge danger-badge">EXCEEDED</span></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<div class="footer">
  Team: Tanishq Gupta (992505170075) · Rajat Singh (992505170083) ·
  Shrestha Tiwari (992505170062) · Abhinav Rathore (992505170061)<br>
  Database Management System Lab (23B61CA125) · BCA 3rd Semester · JIIT
</div>

<?php endif; endif; ?>
</body>
</html>

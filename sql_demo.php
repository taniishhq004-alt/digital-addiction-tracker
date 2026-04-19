<?php
// sql_demo.php  — Show all SQL queries running live (great for teacher demo!)
$activePage = 'sql';
require 'includes/db.php';

// ── Run all queries ──────────────────────────────────────
$queries = [
  [
    'title' => 'Q1 — Total screen time per user',
    'sql'   => "SELECT u.full_name, ROUND(SUM(r.hours_used),2) AS total_hours
FROM users u
JOIN usage_records r ON u.user_id = r.user_id
GROUP BY u.user_id, u.full_name
ORDER BY total_hours DESC",
    'sql_display' => '<span class="kw">SELECT</span> u.full_name, <span class="fn">ROUND</span>(<span class="fn">SUM</span>(r.hours_used),2) <span class="kw">AS</span> total_hours
<span class="kw">FROM</span> users u
<span class="kw">JOIN</span> usage_records r <span class="kw">ON</span> u.user_id = r.user_id
<span class="kw">GROUP BY</span> u.user_id, u.full_name
<span class="kw">ORDER BY</span> total_hours <span class="kw">DESC</span>',
  ],
  [
    'title' => 'Q2 — Category-wise total usage (all users)',
    'sql'   => "SELECT c.icon, c.cat_name, ROUND(SUM(r.hours_used),2) AS total_hours
FROM categories c
JOIN usage_records r ON c.cat_id = r.cat_id
GROUP BY c.cat_id, c.cat_name, c.icon
ORDER BY total_hours DESC",
    'sql_display' => '<span class="kw">SELECT</span> c.icon, c.cat_name, <span class="fn">ROUND</span>(<span class="fn">SUM</span>(r.hours_used),2) <span class="kw">AS</span> total_hours
<span class="kw">FROM</span> categories c
<span class="kw">JOIN</span> usage_records r <span class="kw">ON</span> c.cat_id = r.cat_id
<span class="kw">GROUP BY</span> c.cat_id, c.cat_name, c.icon
<span class="kw">ORDER BY</span> total_hours <span class="kw">DESC</span>',
  ],
  [
    'title' => 'Q3 — Today\'s usage for all users',
    'sql'   => "SELECT u.full_name, c.cat_name, r.app_name, r.hours_used
FROM usage_records r
JOIN users u ON r.user_id = u.user_id
JOIN categories c ON r.cat_id = c.cat_id
WHERE r.usage_date = CURDATE()
ORDER BY r.hours_used DESC",
    'sql_display' => '<span class="kw">SELECT</span> u.full_name, c.cat_name, r.app_name, r.hours_used
<span class="kw">FROM</span> usage_records r
<span class="kw">JOIN</span> users u <span class="kw">ON</span> r.user_id = u.user_id
<span class="kw">JOIN</span> categories c <span class="kw">ON</span> r.cat_id = c.cat_id
<span class="kw">WHERE</span> r.usage_date = <span class="fn">CURDATE</span>()
<span class="kw">ORDER BY</span> r.hours_used <span class="kw">DESC</span>',
  ],
  [
    'title' => 'Q4 — Users who exceeded daily limit today',
    'sql'   => "SELECT u.full_name, c.cat_name,
       ROUND(SUM(r.hours_used),1) AS used_today,
       l.daily_limit
FROM usage_records r
JOIN users u ON r.user_id = u.user_id
JOIN categories c ON r.cat_id = c.cat_id
JOIN usage_limits l ON r.user_id = l.user_id AND r.cat_id = l.cat_id
WHERE r.usage_date = CURDATE() AND l.daily_limit > 0
GROUP BY u.user_id, u.full_name, c.cat_id, c.cat_name, l.daily_limit
HAVING ROUND(SUM(r.hours_used),1) >= l.daily_limit",
    'sql_display' => '<span class="kw">SELECT</span> u.full_name, c.cat_name,
       <span class="fn">ROUND</span>(<span class="fn">SUM</span>(r.hours_used),1) <span class="kw">AS</span> used_today, l.daily_limit
<span class="kw">FROM</span> usage_records r
<span class="kw">JOIN</span> users u <span class="kw">ON</span> r.user_id = u.user_id
<span class="kw">JOIN</span> categories c <span class="kw">ON</span> r.cat_id = c.cat_id
<span class="kw">JOIN</span> usage_limits l <span class="kw">ON</span> r.user_id = l.user_id <span class="kw">AND</span> r.cat_id = l.cat_id
<span class="kw">WHERE</span> r.usage_date = <span class="fn">CURDATE</span>() <span class="kw">AND</span> l.daily_limit > 0
<span class="kw">GROUP BY</span> u.user_id, c.cat_id, l.daily_limit
<span class="kw">HAVING</span> <span class="fn">ROUND</span>(<span class="fn">SUM</span>(r.hours_used),1) >= l.daily_limit',
  ],
  [
    'title' => 'Q5 — Most addicted user (highest 7-day screen time)',
    'sql'   => "SELECT u.full_name, ROUND(SUM(r.hours_used),2) AS week_hours
FROM users u
JOIN usage_records r ON u.user_id = r.user_id
WHERE r.usage_date >= CURDATE() - INTERVAL 7 DAY
GROUP BY u.user_id
ORDER BY week_hours DESC
LIMIT 1",
    'sql_display' => '<span class="kw">SELECT</span> u.full_name, <span class="fn">ROUND</span>(<span class="fn">SUM</span>(r.hours_used),2) <span class="kw">AS</span> week_hours
<span class="kw">FROM</span> users u
<span class="kw">JOIN</span> usage_records r <span class="kw">ON</span> u.user_id = r.user_id
<span class="kw">WHERE</span> r.usage_date >= <span class="fn">CURDATE</span>() - <span class="kw">INTERVAL</span> 7 <span class="kw">DAY</span>
<span class="kw">GROUP BY</span> u.user_id
<span class="kw">ORDER BY</span> week_hours <span class="kw">DESC</span>
<span class="kw">LIMIT</span> 1',
  ],
  [
    'title' => 'Q6 — Average daily usage per category (last 7 days)',
    'sql'   => "SELECT c.icon, c.cat_name,
       ROUND(SUM(r.hours_used) / 7, 2) AS avg_daily_hrs
FROM categories c
JOIN usage_records r ON c.cat_id = r.cat_id
WHERE r.usage_date >= CURDATE() - INTERVAL 7 DAY
GROUP BY c.cat_id, c.cat_name, c.icon
ORDER BY avg_daily_hrs DESC",
    'sql_display' => '<span class="kw">SELECT</span> c.icon, c.cat_name,
       <span class="fn">ROUND</span>(<span class="fn">SUM</span>(r.hours_used) / 7, 2) <span class="kw">AS</span> avg_daily_hrs
<span class="kw">FROM</span> categories c
<span class="kw">JOIN</span> usage_records r <span class="kw">ON</span> c.cat_id = r.cat_id
<span class="kw">WHERE</span> r.usage_date >= <span class="fn">CURDATE</span>() - <span class="kw">INTERVAL</span> 7 <span class="kw">DAY</span>
<span class="kw">GROUP BY</span> c.cat_id, c.cat_name, c.icon
<span class="kw">ORDER BY</span> avg_daily_hrs <span class="kw">DESC</span>',
  ],
];

// Run each query
foreach ($queries as &$q) {
    $res = $conn->query($q['sql']);
    $q['rows'] = [];
    $q['cols'] = [];
    if ($res && $res->num_rows > 0) {
        $first = $res->fetch_assoc();
        $q['cols'] = array_keys($first);
        $q['rows'][] = $first;
        while ($row = $res->fetch_assoc()) $q['rows'][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>SQL Queries — Digital Addiction Tracker</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="layout">
  <?php require 'includes/sidebar.php'; ?>
  <main class="main">
    <div class="page-title">🗄️ Live SQL Query Demo</div>
    <div class="page-sub">These queries run live against the MySQL database — great for teacher demo and viva!</div>

    <div class="alert info" style="margin-bottom:18px">
      💡 All these SQL queries run in real-time using PHP + MySQL (XAMPP). The results below are fetched directly from the database.
    </div>

    <?php foreach ($queries as $i => $q): ?>
    <div class="card-box">
      <h3>🔷 <?= htmlspecialchars($q['title']) ?></h3>

      <!-- SQL Code Block -->
      <div class="sql-block"><?= $q['sql_display'] ?></div>

      <!-- Results Table -->
      <?php if (!empty($q['rows'])): ?>
        <div style="font-size:.68rem;color:var(--text2);margin-bottom:6px;font-weight:700">
          RESULT — <?= count($q['rows']) ?> row(s) returned:
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <?php foreach ($q['cols'] as $col): ?>
                  <th><?= htmlspecialchars($col) ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($q['rows'] as $row): ?>
                <tr>
                  <?php foreach ($q['cols'] as $col): ?>
                    <td><?= htmlspecialchars($row[$col] ?? '') ?></td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="alert warn">No results returned (possibly no data matches today's date yet).</div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>

  </main>
</div>
</body>
</html>

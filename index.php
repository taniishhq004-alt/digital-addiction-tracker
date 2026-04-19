<?php
// index.php — Dashboard
$activePage = 'dashboard';
require 'includes/db.php';

// ── SQL QUERY 1: Stats ──────────────────────────────────
$totalUsers   = $conn->query("SELECT COUNT(*) AS c FROM users")->fetch_assoc()['c'];
$totalRecords = $conn->query("SELECT COUNT(*) AS c FROM usage_records")->fetch_assoc()['c'];

$todayHrs = $conn->query("
    SELECT ROUND(COALESCE(SUM(hours_used),0),1) AS t
    FROM usage_records
    WHERE usage_date = CURDATE()
")->fetch_assoc()['t'];

$totalHrs = $conn->query("
    SELECT ROUND(COALESCE(SUM(hours_used),0),1) AS t
    FROM usage_records
")->fetch_assoc()['t'];

// ── SQL QUERY 2: Category-wise totals ──────────────────
$catResult = $conn->query("
    SELECT c.cat_name, c.icon,
           ROUND(COALESCE(SUM(r.hours_used),0),1) AS total_hrs,
           ROUND(COALESCE(SUM(CASE WHEN r.usage_date=CURDATE() THEN r.hours_used ELSE 0 END),0),1) AS today_hrs
    FROM categories c
    LEFT JOIN usage_records r ON c.cat_id = r.cat_id
    GROUP BY c.cat_id, c.cat_name, c.icon
    ORDER BY total_hrs DESC
");
$cats = [];
$maxHrs = 0;
while ($row = $catResult->fetch_assoc()) {
    $cats[] = $row;
    if ($row['total_hrs'] > $maxHrs) $maxHrs = $row['total_hrs'];
}

// ── SQL QUERY 3: Limit violations today ────────────────
$violationsResult = $conn->query("
    SELECT u.full_name, c.cat_name, c.icon,
           ROUND(SUM(r.hours_used),1) AS used_today,
           l.daily_limit
    FROM usage_records r
    JOIN users u         ON r.user_id = u.user_id
    JOIN categories c    ON r.cat_id  = c.cat_id
    JOIN usage_limits l  ON r.user_id = l.user_id AND r.cat_id = l.cat_id
    WHERE r.usage_date = CURDATE()
      AND l.daily_limit > 0
    GROUP BY u.user_id, u.full_name, c.cat_id, c.cat_name, c.icon, l.daily_limit
    HAVING ROUND(SUM(r.hours_used),1) >= l.daily_limit * 0.8
    ORDER BY (ROUND(SUM(r.hours_used),1) - l.daily_limit) DESC
");
$violations = [];
while ($row = $violationsResult->fetch_assoc()) $violations[] = $row;

// ── SQL QUERY 4: Top 5 categories this week ─────────────
$top5Result = $conn->query("
    SELECT c.icon, c.cat_name, ROUND(SUM(r.hours_used),1) AS total_hrs
    FROM categories c
    JOIN usage_records r ON c.cat_id = r.cat_id
    WHERE r.usage_date >= CURDATE() - INTERVAL 7 DAY
    GROUP BY c.cat_id, c.cat_name, c.icon
    ORDER BY total_hrs DESC
    LIMIT 5
");
$top5 = [];
while ($row = $top5Result->fetch_assoc()) $top5[] = $row;

// ── SQL QUERY 5: CardSwap cards data ────────────────────
$cardData = $conn->query("
    SELECT u.full_name,
           ROUND(SUM(r.hours_used),1) AS total_hrs,
           (
               SELECT c2.icon FROM categories c2
               JOIN usage_records r2 ON c2.cat_id = r2.cat_id
               WHERE r2.user_id = u.user_id
               GROUP BY c2.cat_id
               ORDER BY SUM(r2.hours_used) DESC
               LIMIT 1
           ) AS top_icon,
           (
               SELECT c2.cat_name FROM categories c2
               JOIN usage_records r2 ON c2.cat_id = r2.cat_id
               WHERE r2.user_id = u.user_id
               GROUP BY c2.cat_id
               ORDER BY SUM(r2.hours_used) DESC
               LIMIT 1
           ) AS top_cat
    FROM users u
    LEFT JOIN usage_records r ON u.user_id = r.user_id
    GROUP BY u.user_id, u.full_name
    ORDER BY total_hrs DESC
");
$cardUsers = [];
while ($row = $cardData->fetch_assoc()) $cardUsers[] = $row;

// Bar colors
$barColors = ['#5b5ef4','#ef4444','#f59e0b','#10b981','#8b5cf6','#06b6d4','#f97316','#ec4899','#64748b','#22c55e'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Digital Addiction Tracker</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="components/CardSwap.css">
  <!-- GSAP for CardSwap animation -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
</head>
<body>
<div class="layout">
  <?php require 'includes/sidebar.php'; ?>

  <main class="main">

    <!-- ── HERO with CardSwap ── -->
    <div class="hero">
      <div class="hero-left">
        <div class="project-tag">📚 BCA 2nd Sem &nbsp;|&nbsp; DBMS Minor Project</div>
        <h1>Digital Addiction<br>Tracking Database</h1>
        <p>Monitor screen time, set daily limits, and analyze digital usage patterns.<br>
           Jaypee Institute of Information Technology &nbsp;·&nbsp; Session 2025-26</p>
        <div style="display:flex;gap:10px;margin-top:16px;flex-wrap:wrap">
          <?php foreach(['Tanishq Gupta','Rajat Singh','Shrestha Tiwari','Abhinav Rathore'] as $nm): ?>
            <span class="badge info" style="font-size:.68rem"><?= $nm ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- CardSwap animated cards (PHP data) -->
      <div class="hero-right" id="card-swap-mount"></div>
    </div>

    <!-- ── STATS ROW ── -->
    <div class="stats-row">
      <div class="stat">
        <div class="s-val"><?= $totalUsers ?></div>
        <div class="s-lbl">Total Users</div>
      </div>
      <div class="stat">
        <div class="s-val"><?= $todayHrs ?>h</div>
        <div class="s-lbl">Today's Screen Time</div>
      </div>
      <div class="stat">
        <div class="s-val"><?= $totalRecords ?></div>
        <div class="s-lbl">DB Records</div>
      </div>
      <div class="stat">
        <div class="s-val"><?= $totalHrs ?>h</div>
        <div class="s-lbl">Total All Time</div>
      </div>
      <div class="stat">
        <div class="s-val"><?= count($violations) ?></div>
        <div class="s-lbl">Limit Alerts</div>
      </div>
    </div>

    <!-- ── CATEGORY BARS ── -->
    <div class="card-box">
      <h3>📊 Category-wise Screen Time
        <small style="font-weight:400;color:var(--text2);font-size:.73rem">(SQL: GROUP BY + SUM)</small>
      </h3>
      <?php foreach ($cats as $i => $cat):
        $pct = $maxHrs > 0 ? round($cat['total_hrs'] / $maxHrs * 100) : 0;
        $col = $barColors[$i % count($barColors)];
      ?>
        <div class="bar-wrap">
          <div class="bar-lbl">
            <span><?= $cat['icon'] ?> <?= htmlspecialchars($cat['cat_name']) ?></span>
            <span><?= $cat['total_hrs'] ?>h total &nbsp;·&nbsp; <?= $cat['today_hrs'] ?>h today</span>
          </div>
          <div class="bar-track">
            <div class="bar-fill" style="width:<?= $pct ?>%;background:<?= $col ?>"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">

      <!-- ── LIMIT ALERTS ── -->
      <div class="card-box">
        <h3>⚠️ Limit Alerts Today</h3>
        <?php if (empty($violations)): ?>
          <div class="alert good">✅ No violations today! Everyone is within limits.</div>
        <?php else: foreach ($violations as $v):
          $over = $v['used_today'] >= $v['daily_limit'];
        ?>
          <div class="alert <?= $over ? 'danger' : 'warn' ?>">
            <?= $over ? '🚨' : '⚠️' ?>
            <strong><?= htmlspecialchars($v['full_name']) ?></strong> —
            <?= $v['icon'] ?> <?= htmlspecialchars($v['cat_name']) ?>:
            <?= $v['used_today'] ?>h / <?= $v['daily_limit'] ?>h limit
            <span class="badge <?= $over ? 'danger':'warn' ?>" style="margin-left:6px">
              <?= $over ? 'EXCEEDED':'NEAR LIMIT' ?>
            </span>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <!-- ── TOP 5 THIS WEEK ── -->
      <div class="card-box">
        <h3>🏆 Top 5 Categories (This Week)</h3>
        <?php foreach ($top5 as $i => $row): ?>
          <div style="display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--border)">
            <span style="font-size:.9rem;font-weight:900;color:var(--text2);min-width:22px">#<?= $i+1 ?></span>
            <span style="font-size:1.1rem"><?= $row['icon'] ?></span>
            <span style="flex:1;font-size:.82rem;font-weight:700"><?= htmlspecialchars($row['cat_name']) ?></span>
            <span style="font-size:.82rem;font-weight:800;color:<?= $barColors[$i] ?>"><?= $row['total_hrs'] ?>h</span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </main>
</div>

<!-- ══════════════════════════════════════════════
     CardSwap — vanilla JS implementation
     (uses GSAP loaded above — no React needed)
══════════════════════════════════════════════ -->
<script>
// PHP data → JS
const cardUsers = <?= json_encode($cardUsers) ?>;
const gradients = [
  'linear-gradient(135deg,#5b5ef4,#8b5cf6)',
  'linear-gradient(135deg,#ef4444,#f97316)',
  'linear-gradient(135deg,#10b981,#06b6d4)',
  'linear-gradient(135deg,#f59e0b,#eab308)',
  'linear-gradient(135deg,#ec4899,#8b5cf6)',
];

(function initCardSwap() {
  const mount = document.getElementById('card-swap-mount');
  if (!mount || cardUsers.length === 0) return;

  const W = 240, H = 160;
  const cardDistance   = 40;
  const verticalDist   = 32;
  const delay          = 2800;
  const skewAmount     = 5;

  const container = document.createElement('div');
  container.className = 'card-swap-container';
  container.style.cssText = `width:${W}px;height:${H}px;position:absolute;bottom:0;right:0;`;
  mount.appendChild(container);

  const total = cardUsers.length;
  const els   = [];

  cardUsers.forEach((u, i) => {
    const el = document.createElement('div');
    el.className = 'card';
    el.style.width  = W + 'px';
    el.style.height = H + 'px';
    el.innerHTML = `
      <div class="card-glow-line" style="background:${gradients[i % gradients.length]}"></div>
      <div class="card-inner" style="background:linear-gradient(160deg,rgba(91,94,244,0.12) 0%,rgba(0,0,0,0) 60%)">
        <div class="card-cat-icon">${u.top_icon || '📱'}</div>
        <div class="card-hours">${u.total_hrs}h</div>
        <h3>${u.full_name}</h3>
        <p>Most used: ${u.top_cat || 'N/A'}</p>
        <span class="card-badge ${i===0?'purple':i===1?'red':i===2?'green':'orange'}">
          #${i+1} screen time
        </span>
      </div>`;
    container.appendChild(el);
    els.push(el);
  });

  const makeSlot = (i) => ({
    x:      i * cardDistance,
    y:     -i * verticalDist,
    z:     -i * cardDistance * 1.5,
    zIndex: total - i
  });

  const placeNow = (el, slot) => {
    gsap.set(el, {
      x: slot.x, y: slot.y, z: slot.z,
      xPercent: -50, yPercent: -50,
      skewY: skewAmount,
      transformOrigin: 'center center',
      zIndex: slot.zIndex,
      force3D: true
    });
  };

  els.forEach((el, i) => placeNow(el, makeSlot(i)));

  let order = els.map((_, i) => i);

  function swap() {
    if (order.length < 2) return;
    const [front, ...rest] = order;
    const frontEl = els[front];
    const tl = gsap.timeline();

    tl.to(frontEl, { y: '+=400', duration: 1.4, ease: 'elastic.out(0.6,0.9)' });

    const promoteAt = `-=${1.4 * 0.9}`;
    rest.forEach((idx, i) => {
      const slot = makeSlot(i);
      tl.set(els[idx], { zIndex: slot.zIndex }, promoteAt);
      tl.to(els[idx], { x: slot.x, y: slot.y, z: slot.z, duration: 1.4, ease: 'elastic.out(0.6,0.9)' },
            `+=${i * 0.12}`);
    });

    const backSlot = makeSlot(total - 1);
    tl.call(() => gsap.set(frontEl, { zIndex: backSlot.zIndex }), null, `+=${1.4 * 0.2}`);
    tl.to(frontEl, { x: backSlot.x, y: backSlot.y, z: backSlot.z, duration: 1.4, ease: 'elastic.out(0.6,0.9)' }, '<');
    tl.call(() => { order = [...rest, front]; });
  }

  swap();
  setInterval(swap, delay);
})();
</script>

</body>
</html>

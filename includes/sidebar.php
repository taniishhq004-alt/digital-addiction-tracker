<?php
// includes/sidebar.php
// Pass $activePage variable before including this file
// e.g.  $activePage = 'dashboard';
if (!isset($activePage)) $activePage = 'dashboard';

$pages = [
    'dashboard' => ['ico'=>'🏠', 'label'=>'Dashboard',   'href'=>'index.php'],
    'add'       => ['ico'=>'➕', 'label'=>'Add Usage',    'href'=>'add_usage.php'],
    'users'     => ['ico'=>'👤', 'label'=>'Users',        'href'=>'users.php'],
    'limits'    => ['ico'=>'🚫', 'label'=>'Set Limits',   'href'=>'limits.php'],
    'records'   => ['ico'=>'📋', 'label'=>'All Records',  'href'=>'records.php'],
    'report'    => ['ico'=>'📄', 'label'=>'PDF Report',   'href'=>'report.php'],
    'sql'       => ['ico'=>'🗄️', 'label'=>'SQL Queries',  'href'=>'sql_demo.php'],
];
?>
<div class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">📱</div>
    <h2>Digital Addiction<br>Tracker</h2>
    <span>BCA 2nd Sem · DBMS Project</span>
  </div>
  <nav class="sidebar-nav">
    <?php foreach ($pages as $key => $pg): ?>
      <a href="<?= $pg['href'] ?>" class="<?= $activePage === $key ? 'active' : '' ?>">
        <span class="nav-ico"><?= $pg['ico'] ?></span>
        <span><?= $pg['label'] ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-footer">
    Jaypee Institute of<br>Information Technology<br>
    <strong>Even Semester 2025-26</strong>
  </div>
</div>

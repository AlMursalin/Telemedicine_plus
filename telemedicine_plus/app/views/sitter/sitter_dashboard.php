<?php
require_once __DIR__ . '/../../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}




if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'sitter') {
    header("Location: index.php?route=login");
    exit;
}
$sitter = $_SESSION['user'];

$stmt = $db->prepare("SELECT * FROM sitter_bookings WHERE sitter_name = ? ORDER BY booking_date DESC");
$stmt->execute([$sitter['name']]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sitter Dashboard | Telemedicine++</title>
    <style>
        :root { --primary: #059669; --bg-color: #f8fafc; --text-dark: #1e293b; --text-muted: #64748b; --border-color: #e2e8f0; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-dark); display: flex; min-height: 100vh; }
        .top-navbar { position: fixed; top: 0; left: 0; right: 0; height: 65px; background: var(--primary); color: white; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; z-index: 100; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .top-logo { font-size: 20px; font-weight: bold; color: white; text-decoration: none; }
        .sidebar { width: 250px; background: #ffffff; border-right: 1px solid var(--border-color); position: fixed; top: 65px; bottom: 0; left: 0; padding: 25px 0; display: flex; flex-direction: column; z-index: 90; }
        .sidebar-profile { text-align: center; padding-bottom: 20px; margin-bottom: 10px; }
        .sidebar-avatar { width: 70px; height: 70px; background: #d1fae5; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 10px; font-weight: bold; }
        .sidebar-profile h3 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
        .nav-menu { list-style: none; display: flex; flex-direction: column; }
        .nav-item a { display: flex; align-items: center; gap: 12px; padding: 12px 25px; color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 500; border-left: 3px solid transparent; transition: all 0.2s; }
        .nav-item a:hover { background: #f1f5f9; color: var(--text-dark); } .nav-item a.active { background: #f0fdf4; color: var(--primary); font-weight: 600; border-left-color: var(--primary); }
        .sidebar-divider { height: 1px; background: var(--border-color); margin: 15px 25px; }
        .main-content { margin-top: 65px; margin-left: 250px; flex: 1; padding: 40px; }
        .panel { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        table { width: 100%; border-collapse: collapse; } th, td { padding: 15px 10px; text-align: left; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: #d1fae5; color: #065f46; }
    </style>
</head>
<body>
    <header class="top-navbar">
        <a href="index.php?route=home" class="top-logo">✚ Telemedicine++ Sitter Portal</a>
        <a href="index.php?route=logout" style="color:white; text-decoration:none; font-weight:600;">Log Out</a>
    </header>
    <aside class="sidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($sitter['name'], 0, 1)) ?></div>
            <h3><?= htmlspecialchars($sitter['name']) ?></h3>
            <span>Hospital Sitter</span>
        </div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="index.php?route=sitter_dashboard" class="active">🛏️ Sitter Dashboard</a></li>
            <div class="sidebar-divider"></div>
            <li class="nav-item"><a href="index.php?route=logout">🚪 Log Out</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <h2 style="margin-bottom: 20px;">🛏️ Hospital Sitter Booking Requests</h2>
        <div class="panel">
            <table>
                <tr style="background: #f8fafc;">
                    <th>Booking Date</th>
                    <th>Patient Name</th>
                    <th>Shift Timing</th>
                    <th>Hours</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                </tr>
                <?php if (empty($bookings)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:20px; color:#64748b;">No booking requests found.</td></tr>
                <?php else: ?>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['booking_date']) ?></td>
                        <td><strong><?= htmlspecialchars($b['patient_name']) ?></strong></td>
                        <td><?= htmlspecialchars($b['shift_timing']) ?></td>
                        <td><?= intval($b['hours']) ?> hrs</td>
                        <td><strong>৳<?= number_format($b['total_cost'], 2) ?></strong></td>
                        <td><span class="badge"><?= htmlspecialchars($b['status'] ?? 'Active') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    </main>
</body>
</html>
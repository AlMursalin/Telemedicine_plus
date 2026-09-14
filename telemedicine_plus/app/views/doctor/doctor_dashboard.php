<?php
require_once __DIR__ . '/../../../config/db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') {
    header("Location: index.php?route=login");
    exit;
}
$doctor = $_SESSION['user'];
$today = date('Y-m-d');

$appointments = [];
try {
    $stmt = $db->prepare("SELECT * FROM appointments WHERE doctor_name = ? AND appointment_date = ? ORDER BY appointment_time ASC");
    $stmt->execute([$doctor['name'], $today]);
    $appointments = $stmt->fetchAll();
} catch (Exception $e) {
    $appointments = [];
}

$monday = date('Y-m-d', strtotime('monday this week'));
$sunday = date('Y-m-d', strtotime('sunday this week'));

$week_appointments = [];
try {
    $stmt_week = $db->prepare("SELECT * FROM appointments WHERE doctor_name = ? AND appointment_date BETWEEN ? AND ?");
    $stmt_week->execute([$doctor['name'], $monday, $sunday]);
    $week_appointments = $stmt_week->fetchAll();
} catch (Exception $e) {
    $week_appointments = [];
}

$stat_today_appts = count($appointments);
$stat_total_week = count($week_appointments);
$stat_completed = 0;
$stat_cancelled = 0;
$stat_pending = 0;

$daily_counts = [
    'Mon' => 0, 'Tue' => 0, 'Wed' => 0, 
    'Thu' => 0, 'Fri' => 0, 'Sat' => 0, 'Sun' => 0
];

foreach ($week_appointments as $wa) {
    $status = $wa['status'] ?? 'Scheduled';
    if ($status === 'Completed') {
        $stat_completed++;
    } elseif ($status === 'Cancelled') {
        $stat_cancelled++;
    } else {
        $stat_pending++;
    }
    $day_name = date('D', strtotime($wa['appointment_date']));
    if (isset($daily_counts[$day_name])) {
        $daily_counts[$day_name]++;
    }
}

try {
    $stmt_pat = $db->prepare("SELECT COUNT(DISTINCT patient_name) as total_p FROM appointments WHERE doctor_name = ?");
    $stmt_pat->execute([$doctor['name']]);
    $res_pat = $stmt_pat->fetch();
    $stat_total_patients = $res_pat['total_p'] ?? 0;
} catch (Exception $e) {
    $stat_total_patients = 0;
}

$max_val = max(max($daily_counts), 5);
$chart_width = 500;
$chart_height = 160;
$x_step = $chart_width / 6;

$points = [];
$i = 0;
foreach ($daily_counts as $day => $count) {
    $x = $i * $x_step;
    $y = $chart_height - (($count / $max_val) * ($chart_height - 30)) - 10;
    $points[] = "$x,$y";
    $i++;
}
$polyline_points = implode(' ', $points);
$polygon_points = "0,$chart_height " . $polyline_points . " $chart_width,$chart_height";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Portal | Telemedicine++</title>
    <style>
        :root { --primary: #0d9488; --bg-color: #f8fafc; --text-dark: #1e293b; --text-muted: #64748b; --border-color: #e2e8f0; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-dark); display: flex; min-height: 100vh; }
        
        .top-navbar { position: fixed; top: 0; left: 0; right: 0; height: 65px; background: var(--primary); color: white; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; z-index: 100; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .top-logo-area { display: flex; align-items: center; gap: 30px; }
        .top-logo { font-size: 20px; font-weight: bold; color: white; text-decoration: none; }
        .top-subtitle { font-size: 16px; color: #ccfbf1; border-left: 1px solid #14b8a6; padding-left: 20px; }
        .top-nav-links { display: flex; gap: 25px; align-items: center; list-style: none; font-size: 14px; font-weight: 500; }
        .top-nav-links a { color: #fff; text-decoration: none; opacity: 0.9; }
        .top-nav-links a:hover { opacity: 1; }

        .sidebar { width: 250px; background: #ffffff; border-right: 1px solid var(--border-color); position: fixed; top: 65px; bottom: 0; left: 0; padding: 25px 0; display: flex; flex-direction: column; z-index: 90; }
        .sidebar-profile { text-align: center; padding-bottom: 20px; margin-bottom: 10px; }
        .sidebar-avatar { width: 70px; height: 70px; background: #ccfbf1; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 10px; font-weight: bold; }
        .sidebar-profile h3 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
        .sidebar-profile span { font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 8px; }
        .status-dot { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; color: #059669; font-weight: 600; }
        .status-dot::before { content: ''; width: 8px; height: 8px; background: #10b981; border-radius: 50%; }

        .nav-menu { list-style: none; display: flex; flex-direction: column; }
        .nav-item a { display: flex; align-items: center; gap: 12px; padding: 12px 25px; color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 500; border-left: 3px solid transparent; transition: all 0.2s; }
        .nav-item a:hover { background: #f1f5f9; color: var(--text-dark); }
        .nav-item a.active { background: #ecfdf5; color: var(--primary); font-weight: 600; border-left-color: var(--primary); }
        .sidebar-divider { height: 1px; background: var(--border-color); margin: 15px 25px; }

        .main-content { margin-top: 65px; margin-left: 250px; flex: 1; padding: 30px 40px; }
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; }
        .welcome-text h1 { font-size: 24px; font-weight: 800; margin-bottom: 5px; }
        .welcome-text p { color: var(--text-muted); font-size: 14px; }
        .date-badge { background: white; border: 1px solid var(--border-color); padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; }

        .stats-grid { display: flex; gap: 15px; margin-bottom: 30px; }
        .stat-card { flex: 1; background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; display: flex; align-items: flex-start; gap: 15px; }
        .stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .ic-1 { background: #e0f2fe; color: #0284c7; } .ic-2 { background: #f3e8ff; color: #9333ea; }
        .ic-3 { background: #e0e7ff; color: #4f46e5; } .ic-4 { background: #fef3c7; color: #d97706; }
        .ic-5 { background: #dcfce7; color: #16a34a; }
        .stat-details h5 { font-size: 12px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; }
        .stat-details .val { font-size: 24px; font-weight: 800; margin-bottom: 4px; }
        .stat-details .link { font-size: 11px; color: var(--primary); font-weight: 600; text-decoration: none; }

        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .panel { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; }
        .panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .panel-header h3 { font-size: 16px; font-weight: 700; }
        .panel-header a { font-size: 12px; color: var(--primary); font-weight: 600; text-decoration: none; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 10px; text-align: left; font-size: 13.5px; border-bottom: 1px solid #f1f5f9; }
        .col-muted { color: var(--text-muted); }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; }
        .badge-completed { background: #dcfce7; color: #16a34a; }
        .badge-progress { background: #e0f2fe; color: #0284c7; }
        .badge-scheduled { background: #fef3c7; color: #d97706; }
        .btn-edit { font-size: 12px; color: #fff; background: var(--primary); padding: 5px 10px; border-radius: 6px; text-decoration: none; }

        .chart-container { position: relative; height: 180px; width: 100%; border-bottom: 1px solid var(--border-color); border-left: 1px solid var(--border-color); padding-top: 10px; }
        .chart-labels { display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-top: 8px; padding: 0 5px; }
        .overview-stats { display: flex; gap: 10px; margin-top: 20px; }
        .ov-stat { flex: 1; text-align: center; padding: 12px; border-radius: 8px; background: #f8fafc; }
        .ov-stat .val { font-size: 18px; font-weight: 800; }
        .ov-stat .lbl { font-size: 11px; color: var(--text-muted); font-weight: 600; margin-top: 4px; }
        .view-full-link { display: block; text-align: center; margin-top: 15px; font-size: 13px; color: var(--primary); font-weight: 600; text-decoration: none; }
    </style>
</head>
<body>

    <header class="top-navbar">
        <div class="top-logo-area">
            <a href="index.php?route=home" class="top-logo">✚ Telemedicine++</a>
            <span class="top-subtitle">Doctor Portal</span>
        </div>
        <ul class="top-nav-links">
            <li><a href="index.php?route=home">🏠 Home</a></li>
            <li><a href="index.php?route=doctor_dashboard">📊 Doctor Dashboard</a></li>
            <li><a href="index.php?route=logout">🚪 Log Out</a></li>
        </ul>
    </header>

    <aside class="sidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($doctor['name'], 0, 1)) ?></div>
            <h3><?= htmlspecialchars($doctor['name']) ?></h3>
            <span><?= htmlspecialchars(($doctor['specialty'] && $doctor['specialty'] !== 'Patient') ? $doctor['specialty'] : 'Doctor') ?></span>
            <div class="status-dot">Online</div>
        </div>

        <?php $current_route = $_GET['route'] ?? 'doctor_dashboard'; ?>
        <ul class="nav-menu">
            <li class="nav-item"><a href="index.php?route=doctor_dashboard" class="<?= $current_route == 'doctor_dashboard' ? 'active' : '' ?>">📑 Dashboard</a></li>
            <li class="nav-item"><a href="index.php?route=doctor_appointments" class="<?= $current_route == 'doctor_appointments' ? 'active' : '' ?>">📅 Patient Appointments</a></li>
            <li class="nav-item"><a href="index.php?route=medical_records" class="<?= $current_route == 'medical_records' ? 'active' : '' ?>">📁 Medical Records</a></li>
            <div class="sidebar-divider"></div>
            <li class="nav-item"><a href="index.php?route=logout">🚪 Log Out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <div class="welcome-text">
                <h1>Welcome back, <?= htmlspecialchars($doctor['name']) ?>! 👋</h1>
                <p>Here's what's happening with your practice today.</p>
            </div>
            <div class="date-badge">📅 <?= date('M j, Y | l') ?></div>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-icon ic-1">🩺</div><div class="stat-details"><h5>Today's Appointments</h5><div class="val"><?= $stat_today_appts ?></div><a href="index.php?route=doctor_appointments" class="link">View schedule &rarr;</a></div></div>
            <div class="stat-card"><div class="stat-icon ic-2">🧑‍🤝‍🧑</div><div class="stat-details"><h5>Total Patients</h5><div class="val"><?= $stat_total_patients ?></div><span class="link" style="color: var(--text-muted)">All time</span></div></div>
            <div class="stat-card"><div class="stat-icon ic-3">📅</div><div class="stat-details"><h5>This Week's Total</h5><div class="val"><?= $stat_total_week ?></div><span class="link" style="color: var(--text-muted)">Mon - Sun</span></div></div>
            <div class="stat-card"><div class="stat-icon ic-4">✅</div><div class="stat-details"><h5>Completed</h5><div class="val"><?= $stat_completed ?></div><span class="link" style="color: var(--text-muted)">This Week</span></div></div>
            <div class="stat-card"><div class="stat-icon ic-5">❌</div><div class="stat-details"><h5>Cancelled</h5><div class="val"><?= $stat_cancelled ?></div><span class="link" style="color: var(--text-muted)">This Week</span></div></div>
        </div>

        <div class="dashboard-grid">
            <div class="panel">
                <div class="panel-header">
                    <h3>📅 Today's Appointments</h3>
                    <a href="index.php?route=doctor_appointments">View all &rarr;</a>
                </div>
                <table>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                            <tr><td colspan="5" style="text-align:center; padding: 20px; color: #64748b;">No appointments scheduled for today.</td></tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $app): ?>
                                <tr>
                                    <td class="col-muted"><?= date("h:i A", strtotime($app['appointment_time'])) ?></td>
                                    <td><strong><?= htmlspecialchars($app['patient_name']) ?></strong></td>
                                    <td class="col-muted"><?= htmlspecialchars($app['patient_condition'] ?? 'Consultation') ?></td>
                                    <td>
                                        <?php 
                                            $status = $app['status'] ?? 'Scheduled';
                                            $badge_class = 'badge-scheduled';
                                            if ($status === 'Completed') $badge_class = 'badge-completed';
                                            if ($status === 'In Progress') $badge_class = 'badge-progress';
                                        ?>
                                        <span class="badge <?= $badge_class ?>"><?= $status ?></span>
                                    </td>
                                    <td><a href="index.php?route=manage_appointment&id=<?= $app['id'] ?>" class="btn-edit">Edit/Prescribe</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <a href="index.php?route=doctor_appointments" class="view-full-link">View full schedule &rarr;</a>
            </div>

            <div class="panel">
                <div class="panel-header">
                    <h3>📈 Appointment Overview</h3>
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; background: #f1f5f9; padding: 4px 8px; border-radius: 6px;">This Week</span>
                </div>
                
                <div class="chart-container">
                    <svg viewBox="0 0 500 160" width="100%" height="100%" preserveAspectRatio="none" style="overflow: visible;">
                        <defs>
                            <linearGradient id="chartGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#0d9488" stop-opacity="0.35" />
                                <stop offset="100%" stop-color="#0d9488" stop-opacity="0.0" />
                            </linearGradient>
                        </defs>
                        <polygon points="<?= $polygon_points ?>" fill="url(#chartGrad)" />
                        <polyline fill="none" stroke="#0d9488" stroke-width="3" points="<?= $polyline_points ?>" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>

                <div class="chart-labels">
                    <span>Mon (<?= $daily_counts['Mon'] ?>)</span>
                    <span>Tue (<?= $daily_counts['Tue'] ?>)</span>
                    <span>Wed (<?= $daily_counts['Wed'] ?>)</span>
                    <span>Thu (<?= $daily_counts['Thu'] ?>)</span>
                    <span>Fri (<?= $daily_counts['Fri'] ?>)</span>
                    <span>Sat (<?= $daily_counts['Sat'] ?>)</span>
                    <span>Sun (<?= $daily_counts['Sun'] ?>)</span>
                </div>

                <div class="overview-stats">
                    <div class="ov-stat" style="background:#f1f5f9;"><div class="val"><?= $stat_total_week ?></div><div class="lbl">Total This Week</div></div>
                    <div class="ov-stat" style="background:#ecfdf5;"><div class="val"><?= $stat_completed ?></div><div class="lbl">Completed</div></div>
                    <div class="ov-stat" style="background:#fef2f2;"><div class="val"><?= $stat_cancelled ?></div><div class="lbl">Cancelled</div></div>
                    <div class="ov-stat" style="background:#fffbeb;"><div class="val"><?= $stat_pending ?></div><div class="lbl">Pending</div></div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
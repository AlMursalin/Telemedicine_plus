<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments | Telemedicine++</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; min-height: 100vh; overflow-x: hidden; }
        
        .top-navbar { position: fixed; top: 0; left: 0; right: 0; height: 65px; background: #1e40af; color: white; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .top-logo { font-size: 20px; font-weight: bold; color: white; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .top-nav-links { display: flex; gap: 25px; align-items: center; list-style: none; font-size: 14px; font-weight: 500; }
        .top-nav-links a { color: #e2e8f0; text-decoration: none; transition: color 0.2s; }
        .top-nav-links a:hover, .top-nav-links a.active { color: #ffffff; font-weight: 600; }

        .sidebar { width: 260px; background: #ffffff; border-right: 1px solid #e2e8f0; position: fixed; top: 65px; bottom: 0; left: 0; padding: 25px 15px; display: flex; flex-direction: column; z-index: 90; overflow-y: auto; }
        .sidebar-profile { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; margin-bottom: 20px; }
        .sidebar-avatar { width: 65px; height: 65px; background: #dbeafe; color: #1d4ed8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 10px; font-weight: bold; }
        .sidebar-profile h3 { font-size: 15px; color: #1e293b; font-weight: 700; margin-bottom: 2px; }
        .sidebar-profile span { font-size: 12px; color: #3b82f6; font-weight: 600; text-transform: uppercase; }

        .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 5px; }
        .nav-item a { display: flex; align-items: center; gap: 12px; padding: 11px 15px; border-radius: 8px; color: #64748b; text-decoration: none; font-size: 13.5px; font-weight: 500; transition: all 0.2s; }
        .nav-item a:hover, .nav-item a.active { background: #eff6ff; color: #1d4ed8; font-weight: 600; }
        .sidebar-divider { height: 1px; background: #f1f5f9; margin: 15px 0; }

        .main-content { margin-top: 65px; margin-left: 260px; flex: 1; padding: 30px; background: #f8fafc; min-height: calc(100vh - 65px); }
        .page-header { margin-bottom: 25px; }
        .page-header h1 { font-size: 26px; color: #0f172a; font-weight: 800; margin-bottom: 4px; }
        .page-header p { font-size: 14px; color: #64748b; }

        .card { background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        table { width: 100%; border-collapse: collapse; text-align: left; margin-top: 10px; }
        th { background: #f8fafc; padding: 12px 15px; font-size: 13px; font-weight: 700; color: #475569; border-bottom: 1px solid #e2e8f0; }
        td { padding: 14px 15px; font-size: 13.5px; color: #1e293b; border-bottom: 1px solid #f1f5f9; }
        tr:hover td { background: #f8fafc; }
        
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 600; display: inline-block; }
        .badge-scheduled { background: #dbeafe; color: #1e40af; }
        .badge-completed { background: #dcfce7; color: #166534; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

    <header class="top-navbar">
        <a href="index.php?route=home" class="top-logo"><span>Telemedicine++</span></a>
        <ul class="top-nav-links">
            <li><a href="index.php?route=home">🏠 Home</a></li>
            <li><a href="index.php?route=patient_dashboard">📊 Dashboard</a></li>
            <li><a href="index.php?route=logout" style="color: #fca5a5;">Log Out (<?= htmlspecialchars($user['name'] ?? 'Patient') ?>)</a></li>
        </ul>
    </header>

    <aside class="sidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($user['name'] ?? 'P', 0, 1)) ?></div>
            <h3><?= htmlspecialchars($user['name'] ?? 'Patient') ?></h3>
            <span>Patient</span>
        </div>

        <ul class="nav-menu">
            <li class="nav-item"><a href="index.php?route=patient_dashboard">📊 Dashboard</a></li>
            <li class="nav-item"><a href="index.php?route=patient_appointments" class="active">📅 Track Schedule</a></li>
            <li class="nav-item"><a href="index.php?route=profile">👤 Edit Profile</a></li>
            <li class="nav-item"><a href="index.php?route=patient_appointments">📋 Appointments</a></li>
            <li class="nav-item"><a href="index.php?route=meeting">📹 Live Appointment</a></li>
            <li class="nav-item"><a href="index.php?route=medicines">💳 Make Payment</a></li>
            <div class="sidebar-divider"></div>
            <li class="nav-item"><a href="index.php?route=logout">🚪 Log Out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>My Appointments & Schedule</h1>
            <p>Track your upcoming and past doctor appointments.</p>
        </div>

        <div class="card">
            <?php if (empty($appointments)): ?>
                <p style="color: #64748b; font-size: 14px; text-align: center; padding: 20px;">You have no appointments booked yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Doctor Name</th>
                            <th>Specialty</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Fee</th>
                            <th>Condition</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $app): ?>
                            <tr>
                                <td><strong>Dr. <?= htmlspecialchars($app['doctor_name']) ?></strong></td>
                                <td><?= htmlspecialchars($app['specialty']) ?></td>
                                <td><?= htmlspecialchars($app['appointment_date']) ?></td>
                                <td><?= htmlspecialchars($app['appointment_time']) ?></td>
                                <td>৳<?= number_format($app['fee'] ?? 1000, 2) ?></td>
                                <td><?= htmlspecialchars($app['patient_condition'] ?? 'N/A') ?></td>
                                <td>
                                    <?php 
                                        $status = $app['status'] ?? 'Scheduled';
                                        $badge_class = 'badge-scheduled';
                                        if ($status === 'Completed') $badge_class = 'badge-completed';
                                        if ($status === 'Cancelled') $badge_class = 'badge-cancelled';
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($status) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
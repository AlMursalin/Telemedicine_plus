<?php
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: login.php");
    exit;
}
$patient = $_SESSION['user'];

$appointments = [];
try {
    $stmt = $db->prepare("SELECT * FROM appointments WHERE patient_name = ? ORDER BY appointment_date DESC, appointment_time DESC");
    $stmt->execute([$patient['name']]);
    $appointments = $stmt->fetchAll();
} catch (Exception $e) {
    $appointments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments | Patient Portal</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; min-height: 100vh; }
        
        .top-navbar { position: fixed; top: 0; left: 0; right: 0; height: 65px; background: #1e40af; color: white; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .top-logo { font-size: 20px; font-weight: bold; color: white; text-decoration: none; }

        .sidebar { width: 260px; background: #ffffff; border-right: 1px solid #e2e8f0; position: fixed; top: 65px; bottom: 0; left: 0; padding: 25px 15px; display: flex; flex-direction: column; z-index: 90; }
        .sidebar-profile { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; margin-bottom: 20px; }
        .sidebar-avatar { width: 65px; height: 65px; background: #dbeafe; color: #1d4ed8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 10px; font-weight: bold; }
        .sidebar-profile h3 { font-size: 15px; color: #1e293b; font-weight: 700; margin-bottom: 2px; }
        .sidebar-profile span { font-size: 12px; color: #3b82f6; font-weight: 600; text-transform: uppercase; }

        .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 5px; }
        .nav-item a { display: flex; align-items: center; gap: 12px; padding: 11px 15px; border-radius: 8px; color: #64748b; text-decoration: none; font-size: 13.5px; font-weight: 500; transition: all 0.2s; }
        .nav-item a:hover, .nav-item a.active { background: #eff6ff; color: #1d4ed8; font-weight: 600; }
        
        .main-content { margin-top: 65px; margin-left: 260px; flex: 1; padding: 30px; background: #f8fafc; min-height: calc(100vh - 65px); }
        .panel { background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 14px 12px; text-align: left; font-size: 13.5px; border-bottom: 1px solid #f1f5f9; }
        th { background: #f8fafc; color: #475569; font-weight: 600; }
        td { color: #1e293b; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; display: inline-block; }
        .badge-completed { background: #dcfce7; color: #15803d; }
        .badge-progress { background: #e0f2fe; color: #0284c7; }
        .badge-scheduled { background: #fef3c7; color: #d97706; }
        .btn-book-new { background: #2563eb; color: white; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 600; float: right; }
        .btn-book-new:hover { background: #1d4ed8; }
    </style>
</head>
<body>
    <header class="top-navbar">
        <a href="index.php" class="top-logo">Telemedicine++ Patient Portal</a>
    </header>

    <aside class="sidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($patient['name'], 0, 1)) ?></div>
            <h3><?= htmlspecialchars($patient['name']) ?></h3>
            <span>Patient</span>
        </div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="dashboard.php">📊 Dashboard</a></li>
            <li class="nav-item"><a href="patient_appointments.php" class="active">📋 Appointments</a></li>
            <li class="nav-item"><a href="logout.php">🚪 Log Out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <div>
                <h1 style="font-size: 24px; color: #0f172a; font-weight: 800; margin-bottom: 4px;">My Appointments & History</h1>
                <p style="font-size: 14px; color: #64748b;">View your booked consultations, prescriptions, and doctor notes.</p>
            </div>
            <a href="dashboard.php" class="btn-book-new">+ Book New Appointment</a>
        </div>

        <div class="panel">
            <?php if (empty($appointments)): ?>
                <p style="color: #64748b; padding: 20px; text-align: center;">You have not booked any appointments yet. Go to your dashboard to choose a doctor!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Doctor Name</th>
                            <th>Specialty</th>
                            <th>Date & Time</th>
                            <th>Fee</th>
                            <th>Status</th>
                            <th>Prescription & Advice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $app): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($app['doctor_name']) ?></strong></td>
                                <td><?= htmlspecialchars($app['specialty']) ?></td>
                                <td><?= date("M j, Y", strtotime($app['appointment_date'])) ?> at <?= date("h:i A", strtotime($app['appointment_time'])) ?></td>
                                <td>৳<?= number_format($app['fee'], 2) ?></td>
                                <td>
                                    <?php 
                                        $status = $app['status'] ?? 'Scheduled';
                                        $b_class = 'badge-scheduled';
                                        if ($status === 'Completed') $b_class = 'badge-completed';
                                        if ($status === 'In Progress') $b_class = 'badge-progress';
                                    ?>
                                    <span class="badge <?= $b_class ?>"><?= htmlspecialchars($status) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($app['prescription'])): ?>
                                        <span style="font-size: 12px; color: #0f766e; font-weight: 600;">💊 <?= nl2br(htmlspecialchars($app['prescription'])) ?></span>
                                    <?php else: ?>
                                        <span style="color: #94a3b8; font-size: 12px;">Pending consultation</span>
                                    <?php endif; ?>
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
<?php
require_once 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') { header("Location: login.php"); exit; }
$doctor = $_SESSION['user'];

$stmt = $db->prepare("SELECT patient_name, COUNT(id) as total_visits, MAX(appointment_date) as last_visit FROM appointments WHERE doctor_name = ? GROUP BY patient_name ORDER BY last_visit DESC");
$stmt->execute([$doctor['name']]);
$patients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Patients | Doctor Portal</title>
    <style>
        :root { --primary: #0d9488; --bg-color: #f8fafc; --text-dark: #1e293b; --text-muted: #64748b; --border-color: #e2e8f0; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-dark); display: flex; min-height: 100vh; }
        .top-navbar { position: fixed; top: 0; left: 0; right: 0; height: 65px; background: var(--primary); color: white; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; z-index: 100; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .top-logo-area { display: flex; align-items: center; gap: 30px; } .top-logo { font-size: 20px; font-weight: bold; color: white; text-decoration: none; }
        .sidebar { width: 250px; background: #ffffff; border-right: 1px solid var(--border-color); position: fixed; top: 65px; bottom: 0; left: 0; padding: 25px 0; display: flex; flex-direction: column; z-index: 90; }
        .sidebar-profile { text-align: center; padding-bottom: 20px; margin-bottom: 10px; }
        .sidebar-avatar { width: 70px; height: 70px; background: #ccfbf1; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 10px; font-weight: bold; }
        .sidebar-profile h3 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
        .nav-menu { list-style: none; display: flex; flex-direction: column; }
        .nav-item a { display: flex; align-items: center; gap: 12px; padding: 12px 25px; color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 500; border-left: 3px solid transparent; transition: all 0.2s; }
        .nav-item a:hover { background: #f1f5f9; color: var(--text-dark); } .nav-item a.active { background: #ecfdf5; color: var(--primary); font-weight: 600; border-left-color: var(--primary); }
        .sidebar-divider { height: 1px; background: var(--border-color); margin: 15px 25px; }
        .main-content { margin-top: 65px; margin-left: 250px; flex: 1; padding: 40px; }
        .panel { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        table { width: 100%; border-collapse: collapse; } th, td { padding: 15px 10px; text-align: left; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
    </style>
</head>
 
<body>
    <header class="top-navbar"><div class="top-logo-area"><a href="index.php" class="top-logo">✚ Telemedicine++</a></div></header>
    <aside class="sidebar">
        <div class="sidebar-profile"><div class="sidebar-avatar"><?= strtoupper(substr($doctor['name'], 0, 1)) ?></div><h3><?= htmlspecialchars($doctor['name']) ?></h3></div>
        <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
        <ul class="nav-menu">
            <li class="nav-item"><a href="doctor_dashboard.php" class="<?= $current_page == 'doctor_dashboard.php' ? 'active' : '' ?>">📑 Dashboard</a></li>
            <li class="nav-item"><a href="doctor_appointments.php" class="<?= $current_page == 'doctor_appointments.php' ? 'active' : '' ?>">📅 Patient Appointments</a></li>
            <li class="nav-item"><a href="doctor_prescriptions.php" class="<?= $current_page == 'doctor_prescriptions.php' ? 'active' : '' ?>">💊 Prescriptions</a></li>
            <li class="nav-item"><a href="doctor_patients.php" class="<?= $current_page == 'doctor_patients.php' ? 'active' : '' ?>">👥 Patients</a></li>
            <li class="nav-item"><a href="doctor_medical_records.php" class="<?= $current_page == 'doctor_medical_records.php' ? 'active' : '' ?>">📁 Medical Records</a></li>
            <li class="nav-item"><a href="doctor_messages.php" class="<?= $current_page == 'doctor_messages.php' ? 'active' : '' ?>">✉️ Messages</a></li>
            <div class="sidebar-divider"></div>
            <li class="nav-item"><a href="doctor_profile_setting.php" class="<?= $current_page == 'doctor_profile_setting.php' ? 'active' : '' ?>">⚙️ Profile Settings</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <h2 style="margin-bottom: 20px;">👥 Patient Directory</h2>
        <div class="panel">
            <table>
                <tr style="background: #f8fafc;"><th>Patient Name</th><th>Total Visits</th><th>Last Visit Date</th></tr>
                <?php foreach ($patients as $p): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($p['patient_name']) ?></strong></td>
                    <td><span style="background:#e0f2fe; color:#0284c7; padding:6px 12px; border-radius:20px; font-weight:bold; font-size:12px;"><?= $p['total_visits'] ?> Consultations</span></td>
                    <td style="color:#64748b;"><?= date("F j, Y", strtotime($p['last_visit'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </main>
</body></html>
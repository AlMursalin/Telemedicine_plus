<?php
require_once __DIR__ . '/../../config/db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') {
    header("Location: index.php?route=login");
    exit;
}
$doctor = $_SESSION['user'];
$appointment_id = $_GET['id'] ?? 0;

$success_msg = '';

$stmt = $db->prepare("SELECT * FROM appointments WHERE id = ?");
$stmt->execute([$appointment_id]);
$appointment = $stmt->fetch();

if (!$appointment) {
    die("Appointment not found or you do not have permission.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_condition = trim($_POST['patient_condition']);
    $prescription = trim($_POST['prescription']);
    $doctor_advice = trim($_POST['doctor_advice']);
    $status = trim($_POST['status']);
    
    $update = $db->prepare("UPDATE appointments SET patient_condition = ?, prescription = ?, doctor_advice = ?, status = ? WHERE id = ?");
    $update->execute([$patient_condition, $prescription, $doctor_advice, $status, $appointment_id]);
    
    $success_msg = "Patient record and prescription updated successfully!";
    
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Patient | Doctor Portal</title>
    <style>
        :root { --primary: #0d9488; --bg-color: #f8fafc; --text-dark: #1e293b; --text-muted: #64748b; --border-color: #e2e8f0; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-dark); display: flex; min-height: 100vh; }
        
        .top-navbar { position: fixed; top: 0; left: 0; right: 0; height: 65px; background: var(--primary); color: white; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; z-index: 100; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .top-logo-area { display: flex; align-items: center; gap: 30px; }
        .top-logo { font-size: 20px; font-weight: bold; color: white; text-decoration: none; }
        .top-subtitle { font-size: 16px; color: #ccfbf1; border-left: 1px solid #14b8a6; padding-left: 20px; }
        
        .sidebar { width: 250px; background: #ffffff; border-right: 1px solid var(--border-color); position: fixed; top: 65px; bottom: 0; left: 0; padding: 25px 0; display: flex; flex-direction: column; z-index: 90; }
        .sidebar-profile { text-align: center; padding-bottom: 20px; margin-bottom: 10px; }
        .sidebar-avatar { width: 70px; height: 70px; background: #ccfbf1; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 10px; font-weight: bold; }
        .sidebar-profile h3 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
        .sidebar-profile span { font-size: 12px; color: var(--text-muted); display: block; margin-bottom: 8px; }
        
        .nav-menu { list-style: none; display: flex; flex-direction: column; }
        .nav-item a { display: flex; align-items: center; gap: 12px; padding: 12px 25px; color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 500; border-left: 3px solid transparent; transition: all 0.2s; }
        .nav-item a:hover { background: #f1f5f9; color: var(--text-dark); }
        .nav-item a.active { background: #ecfdf5; color: var(--primary); font-weight: 600; border-left-color: var(--primary); }
        .sidebar-divider { height: 1px; background: var(--border-color); margin: 15px 25px; }

        .main-content { margin-top: 65px; margin-left: 250px; flex: 1; padding: 40px; }
        
        .header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .btn-back { color: var(--text-muted); text-decoration: none; font-weight: 600; font-size: 14px; }
        .split-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; }
        
        .panel { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; }
        .panel h3 { font-size: 16px; font-weight: 700; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px; }
        
        .info-row { margin-bottom: 12px; }
        .info-row label { display: block; font-size: 11px; color: var(--text-muted); font-weight: 700; text-transform: uppercase; }
        .info-row span { font-size: 14px; font-weight: 600; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; }
        .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; outline: none; background: #f8fafc; }
        .btn-save { background: var(--primary); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; width: 100%; }
        
        .alert-success { background: #dcfce7; color: #16a34a; padding: 12px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; text-align: center; border: 1px solid #bbf7d0; }
    </style>
</head>
<body>
    <header class="top-navbar">
        <div class="top-logo-area">
            <a href="index.php?route=home" class="top-logo">✚ Telemedicine++</a>
            <span class="top-subtitle">Doctor Portal</span>
        </div>
    </header>

    <aside class="sidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($doctor['name'], 0, 1)) ?></div>
            <h3><?= htmlspecialchars($doctor['name']) ?></h3>
            <span><?= htmlspecialchars($doctor['specialty']) ?></span>
        </div>
        
        <?php $current_route = $_GET['route'] ?? ''; ?>
        <ul class="nav-menu">
            <li class="nav-item"><a href="index.php?route=doctor_dashboard">📑 Dashboard</a></li>
            <li class="nav-item"><a href="index.php?route=doctor_appointments">📅 Patient Appointments</a></li>
            <li class="nav-item"><a href="index.php?route=medical_records">📁 Medical Records</a></li>
            <div class="sidebar-divider"></div>
            <li class="nav-item"><a href="index.php?route=logout">🚪 Log Out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="header-row">
            <a href="index.php?route=doctor_dashboard" class="btn-back">&larr; Back to Dashboard</a>
        </div>

        <?php if ($success_msg): ?><div class="alert-success"><?= $success_msg ?></div><?php endif; ?>

        <div class="split-grid">
            <div class="panel">
                <h3>👤 Patient Information</h3>
                <div class="info-row"><label>Patient Name</label><span><?= htmlspecialchars($appointment['patient_name']) ?></span></div>
                <div class="info-row"><label>Appointment Date</label><span><?= date("F j, Y", strtotime($appointment['appointment_date'])) ?></span></div>
                <div class="info-row"><label>Time slot</label><span><?= date("h:i A", strtotime($appointment['appointment_time'])) ?></span></div>
                <div class="info-row"><label>Fee</label><span>৳<?= number_format($appointment['fee'], 2) ?></span></div>
            </div>

            <div class="panel">
                <h3>📝 Consultation & Prescription Entry</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Appointment Status</label>
                        <select name="status">
                            <option value="Scheduled" <?= ($appointment['status'] == 'Scheduled') ? 'selected' : '' ?>>Scheduled</option>
                            <option value="In Progress" <?= ($appointment['status'] == 'In Progress') ? 'selected' : '' ?>>In Progress</option>
                            <option value="Completed" <?= ($appointment['status'] == 'Completed') ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Patient Condition / Symptoms</label>
                        <textarea name="patient_condition" rows="2"><?= htmlspecialchars($appointment['patient_condition'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Doctor's Advice / Notes</label>
                        <textarea name="doctor_advice" rows="3"><?= htmlspecialchars($appointment['doctor_advice'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Prescription (Medicines & Dosage)</label>
                        <textarea name="prescription" rows="4"><?= htmlspecialchars($appointment['prescription'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn-save">Update Patient Record</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
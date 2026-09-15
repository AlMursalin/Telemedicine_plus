<?php
require_once 'db.php';


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: login.php");
    exit;
}
$patient = $_SESSION['user'];

$doctor_id = $_GET['doctor_id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'doctor'");
$stmt->execute([$doctor_id]);
$doctor = $stmt->fetch();

if (!$doctor) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Invalid Doctor | Telemedicine++</title>
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
            body { background-color: #f4f6f9; color: #333; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
            .error-card { background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; text-align: center; max-width: 450px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
            .error-card h2 { color: #ef4444; margin-bottom: 12px; font-size: 22px; }
            .error-card p { color: #64748b; font-size: 14px; margin-bottom: 25px; }
            .btn-back { background: #2563eb; color: white; padding: 11px 24px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 14px; display: inline-block; }
            .btn-back:hover { background: #1d4ed8; }
        </style>
    </head>
    <body>
        <div class="error-card">
            <h2>⚠️ Invalid Doctor Selected</h2>
            <p>The doctor you are trying to reach could not be found or is no longer available on the portal.</p>
            <a href="dashboard.php" class="btn-back">Go back to Dashboard</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $patient_condition = trim($_POST['patient_condition']);
    $fee = floatval($doctor['consultation_fee'] ?? 1000);

    try {
        $db->exec("CREATE TABLE IF NOT EXISTS appointments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_name VARCHAR(255) NOT NULL,
            doctor_name VARCHAR(255) NOT NULL,
            specialty VARCHAR(255) NOT NULL,
            appointment_date DATE NOT NULL,
            appointment_time TIME NOT NULL,
            fee DECIMAL(10,2) NOT NULL,
            patient_condition TEXT,
            status VARCHAR(50) DEFAULT 'Scheduled',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $insert = $db->prepare("INSERT INTO appointments (patient_name, doctor_name, specialty, appointment_date, appointment_time, fee, patient_condition, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Scheduled')");
        $insert->execute([
            $patient['name'],
            $doctor['name'],
            $doctor['specialty'] ?? 'General Specialist',
            $appointment_date,
            $appointment_time,
            $fee,
            $patient_condition
        ]);

        $success_msg = "Appointment booked successfully with Dr. " . htmlspecialchars($doctor['name']) . "! Fee: ৳" . number_format($fee, 2);
    } catch (Exception $e) {
        $success_msg = "Error booking appointment. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Appointment | Telemedicine++</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; min-height: 100vh; }
        .top-navbar { position: fixed; top: 0; left: 0; right: 0; height: 65px; background: #2563eb; color: white; display: flex; align-items: center; padding: 0 30px; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .top-logo { font-size: 20px; font-weight: bold; color: white; text-decoration: none; }
        .main-content { margin-top: 65px; flex: 1; padding: 40px; display: flex; justify-content: center; }
        .booking-panel { background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 35px; width: 100%; max-width: 600px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .booking-panel h2 { font-size: 22px; margin-bottom: 20px; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; }
        .doc-summary { background: #eff6ff; padding: 18px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #bfdbfe; }
        .doc-summary p { margin-bottom: 6px; font-size: 14px; color: #1e3a8a; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; background: #fff; }
        .form-group input:focus, .form-group textarea:focus { border-color: #2563eb; }
        .btn-submit { background: #2563eb; color: white; border: none; padding: 14px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 10px; transition: background 0.2s; }
        .btn-submit:hover { background: #1d4ed8; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <header class="top-navbar"><a href="dashboard.php" class="top-logo">✚ Telemedicine++ Patient Portal</a></header>
    <main class="main-content">
        <div class="booking-panel">
            <h2>🩺 Book Doctor Appointment</h2>
            <?php if ($success_msg): ?>
                <div class="alert-success"><?= $success_msg ?></div>
                <a href="dashboard.php" class="btn-submit" style="text-align: center; display: block; text-decoration: none;">Return to Dashboard</a>
            <?php else: ?>
                <div class="doc-summary">
                    <p><strong>Doctor Name:</strong> Dr. <?= htmlspecialchars($doctor['name']) ?></p>
                    <p><strong>Specialty:</strong> <?= htmlspecialchars($doctor['specialty'] ?? 'General Specialist') ?></p>
                    <p><strong>Consultation Fee:</strong> ৳<?= number_format($doctor['consultation_fee'] ?? 1000, 2) ?></p>
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label>Appointment Date</label>
                        <input type="date" name="appointment_date" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Appointment Time</label>
                        <input type="time" name="appointment_time" required>
                    </div>
                    <div class="form-group">
                        <label>Patient Symptoms / Medical Notes</label>
                        <textarea name="patient_condition" rows="3" placeholder="Briefly describe your symptoms or reason for visit..." required></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Confirm & Book Appointment</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
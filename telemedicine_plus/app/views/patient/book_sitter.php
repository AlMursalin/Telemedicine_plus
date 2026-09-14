<?php
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: index.php?route=login");
    exit;
}
$patient = $_SESSION['user'];

$sitter_id = $_GET['sitter_id'] ?? 0;
$stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'sitter'");
$stmt->execute([$sitter_id]);
$sitter = $stmt->fetch();

if (!$sitter) {
    die("Invalid Sitter Selected. <a href='index.php?route=patient_dashboard'>Go back</a>");
}

$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = $_POST['booking_date'];
    $shift_timing = trim($_POST['shift_timing']);
    $hours = intval($_POST['hours']);
    $patient_condition = trim($_POST['patient_condition']);
    
    $hourly_rate = floatval($sitter['hourly_rate'] ?? 500);
    $total_cost = $hourly_rate * $hours;
    
    $insert = $db->prepare("INSERT INTO sitter_bookings (patient_name, sitter_name, hourly_rate, hours, total_cost, booking_date, shift_timing, patient_condition, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
    $insert->execute([
        $patient['name'],
        $sitter['name'],
        $hourly_rate,
        $hours,
        $total_cost,
        $booking_date,
        $shift_timing,
        $patient_condition
    ]);
    
    $success_msg = "Bedside sitter booked successfully! Total Cost: ৳" . number_format($total_cost, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Hospital Sitter | Telemedicine++</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; min-height: 100vh; }
        .top-navbar { position: fixed; top: 0; left: 0; right: 0; height: 65px; background: #059669; color: white; display: flex; align-items: center; padding: 0 30px; z-index: 100; }
        .top-logo { font-size: 20px; font-weight: bold; color: white; text-decoration: none; }
        .main-content { margin-top: 65px; flex: 1; padding: 40px; display: flex; justify-content: center; }
        .booking-panel { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 35px; width: 100%; max-width: 600px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .booking-panel h2 { font-size: 22px; margin-bottom: 20px; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; }
        .doc-summary { background: #f0fdf4; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #bbf7d0; }
        .doc-summary p { margin-bottom: 5px; font-size: 14px; color: #065f46; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; background: #fff; }
        .btn-submit { background: #059669; color: white; border: none; padding: 14px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 10px; }
        .btn-submit:hover { background: #047857; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <header class="top-navbar"><a href="index.php?route=patient_dashboard" class="top-logo">✚ Telemedicine++ Patient Portal</a></header>
    <main class="main-content">
        <div class="booking-panel">
            <h2>🛏️ Request Bedside Hospital Sitter</h2>
            <?php if ($success_msg): ?>
                <div class="alert-success"><?= $success_msg ?></div>
                <a href="index.php?route=patient_dashboard" class="btn-submit" style="text-align: center; display: block; text-decoration: none;">Return to Dashboard</a>
            <?php else: ?>
                <div class="doc-summary">
                    <p><strong>Sitter Name:</strong> <?= htmlspecialchars($sitter['name']) ?></p>
                    <p><strong>Qualification:</strong> <?= htmlspecialchars($sitter['specialty']) ?></p>
                    <p><strong>Hourly Rate:</strong> ৳<?= number_format($sitter['hourly_rate'] ?? 500, 2) ?> / hr</p>
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label>Booking Date</label>
                        <input type="date" name="booking_date" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Shift Timing</label>
                        <input type="text" name="shift_timing" placeholder="e.g. 08:00 AM - 04:00 PM" required>
                    </div>
                    <div class="form-group">
                        <label>Number of Hours</label>
                        <input type="number" name="hours" min="1" max="24" value="4" required>
                    </div>
                    <div class="form-group">
                        <label>Patient Medical Condition / Care Details</label>
                        <textarea name="patient_condition" rows="3" placeholder="Describe patient supervision needs or medical conditions..." required></textarea>
                    </div>
                    <button type="submit" class="btn-submit">Confirm & Book Sitter</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
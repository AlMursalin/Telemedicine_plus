<?php
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'sitter') {
    header("Location: login.php");
    exit;
}



$sitter_session = $_SESSION['user'];
$success_msg = '';

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$sitter_session['id']]);
$sitter_data = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $specialty = trim($_POST['specialty']);
    $hourly_rate = floatval($_POST['hourly_rate']);
    $availability = trim($_POST['availability']);
    
    try {
        $update = $db->prepare("UPDATE users SET name = ?, specialty = ?, hourly_rate = ?, availability = ? WHERE id = ?");
        $update->execute([$name, $specialty, $hourly_rate, $availability, $sitter_session['id']]);
        
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['specialty'] = $specialty;
        $_SESSION['user']['hourly_rate'] = $hourly_rate;
        $_SESSION['user']['availability'] = $availability;
        $success_msg = "Profile, rates, and availability status updated successfully!";
        
        $stmt->execute([$sitter_session['id']]);
        $sitter_data = $stmt->fetch();
    } catch (PDOException $e) {
        $success_msg = "Error updating profile.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile & Availability | Sitter Portal</title>
    <style>
        :root { --primary: #059669; --bg-color: #f8fafc; --text-dark: #1e293b; --text-muted: #64748b; --border-color: #e2e8f0; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-dark); display: flex; min-height: 100vh; }
        .top-navbar { position: fixed; top: 0; left: 0; right: 0; height: 65px; background: var(--primary); color: white; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; z-index: 100; }
        .top-logo { font-size: 20px; font-weight: bold; color: white; text-decoration: none; }
        .sidebar { width: 250px; background: #ffffff; border-right: 1px solid var(--border-color); position: fixed; top: 65px; bottom: 0; left: 0; padding: 25px 0; display: flex; flex-direction: column; }
        .sidebar-profile { text-align: center; padding-bottom: 20px; }
        .sidebar-avatar { width: 70px; height: 70px; background: #d1fae5; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 10px; font-weight: bold; }
        .nav-menu { list-style: none; display: flex; flex-direction: column; }
        .nav-item a { display: flex; align-items: center; gap: 12px; padding: 12px 25px; color: var(--text-muted); text-decoration: none; font-size: 14px; font-weight: 500; border-left: 3px solid transparent; }
        .nav-item a:hover, .nav-item a.active { background: #ecfdf5; color: var(--primary); font-weight: 600; border-left-color: var(--primary); }
        .main-content { margin-top: 65px; margin-left: 250px; flex: 1; padding: 40px; }
        .panel { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 30px; max-width: 600px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: var(--text-muted); margin-bottom: 8px; text-transform: uppercase; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 14px; outline: none; background: #fff; }
        .btn-save { background: var(--primary); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; }
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; }
    </style>
</head>
<body>
    <header class="top-navbar"><a href="index.php" class="top-logo">✚ Telemedicine++ Sitter Portal</a></header>
    <aside class="sidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($sitter_session['name'], 0, 1)) ?></div>
            <h3><?= htmlspecialchars($sitter_session['name']) ?></h3>
            <span><?= htmlspecialchars($sitter_session['specialty']) ?></span>
        </div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="sitter_dashboard.php">📑 Sitter Dashboard</a></li>
            <li class="nav-item"><a href="sitter_profile_setting.php" class="active">⚙️ Profile & Rates</a></li>
            <li class="nav-item"><a href="logout.php">🚪 Log Out</a></li>
        </ul>
    </aside>
    <main class="main-content">
        <div class="panel">
            <h2>⚙️ Edit Profile, Rates & Booking Availability</h2><br>
            <?php if ($success_msg): ?><div class="alert-success"><?= $success_msg ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($sitter_data['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Qualification / Certification</label>
                    <input type="text" name="specialty" value="<?= htmlspecialchars($sitter_data['specialty']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Hourly Rate (৳ Taka)</label>
                    <input type="number" step="0.01" name="hourly_rate" value="<?= htmlspecialchars($sitter_data['hourly_rate'] ?? 500.00) ?>" required>
                </div>
                <div class="form-group">
                    <label>Booking Availability Status</label>
                    <select name="availability" required>
                        <option value="Available" <?= (($sitter_data['availability'] ?? 'Available') === 'Available') ? 'selected' : '' ?>>🟢 Available (On for Booking)</option>
                        <option value="Off Duty" <?= (($sitter_data['availability'] ?? '') === 'Off Duty') ? 'selected' : '' ?>>🔴 Off Duty / Unavailable</option>
                    </select>
                </div>
                <button type="submit" class="btn-save">Save Changes</button>
            </form>
        </div>
    </main>
</body>
</html>
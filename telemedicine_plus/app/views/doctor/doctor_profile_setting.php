<?php
require_once 'db.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') {
    header("Location: login.php");
    exit;
}

$doctor_session = $_SESSION['user'];
$success_msg = '';
$error_msg = '';

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$doctor_session['id']]);
$doctor_data = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $specialty = trim($_POST['specialty']);
    $consultation_fee = floatval($_POST['consultation_fee']);
    
    try {
        $update = $db->prepare("UPDATE users SET name = ?, specialty = ?, consultation_fee = ? WHERE id = ?");
        $update->execute([$name, $specialty, $consultation_fee, $doctor_session['id']]);
        
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['specialty'] = $specialty;
        $_SESSION['user']['consultation_fee'] = $consultation_fee;
        
        $success_msg = "Profile and medical specialty updated successfully!";
        
        $stmt->execute([$doctor_session['id']]);
        $doctor_data = $stmt->fetch();
    } catch (PDOException $e) {
        $error_msg = "Error updating profile: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings | Doctor Portal</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8fafc; color: #1e293b; display: flex; min-height: 100vh; }
        
        .top-navbar { position: fixed; top: 0; left: 0; right: 0; height: 65px; background: #0d9488; color: white; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .top-logo { font-size: 20px; font-weight: bold; color: white; text-decoration: none; display: flex; align-items: center; gap: 8px; }

        .sidebar { width: 260px; background: #ffffff; border-right: 1px solid #e2e8f0; position: fixed; top: 65px; bottom: 0; left: 0; padding: 25px 15px; display: flex; flex-direction: column; z-index: 90; }
        .sidebar-profile { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; margin-bottom: 20px; }
        .sidebar-avatar { width: 65px; height: 65px; background: #ccfbf1; color: #0d9488; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 10px; font-weight: bold; }
        .sidebar-profile h3 { font-size: 15px; color: #0f172a; font-weight: 700; margin-bottom: 2px; }
        .sidebar-profile span { font-size: 12px; color: #0d9488; font-weight: 600; text-transform: uppercase; }

        .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 5px; }
        .nav-item a { display: flex; align-items: center; gap: 12px; padding: 11px 15px; border-radius: 8px; color: #64748b; text-decoration: none; font-size: 13.5px; font-weight: 500; transition: all 0.2s; }
        .nav-item a:hover, .nav-item a.active { background: #f0fdf4; color: #0d9488; font-weight: 600; }
        
        .main-content { margin-top: 65px; margin-left: 260px; flex: 1; padding: 40px; background: #f8fafc; min-height: calc(100vh - 65px); }
        .panel { background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 30px; max-width: 600px; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; background: #fff; }
        .form-group input:focus { border-color: #0d9488; }
        
        .btn-save { background: #0d9488; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
        .btn-save:hover { background: #0f766e; }
        
        .alert-success { background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; }
        .alert-error { background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 8px; font-weight: 600; margin-bottom: 20px; }
    </style>
</head>
<body>
    <header class="top-navbar">
        <a href="index.php" class="top-logo"><span>Telemedicine++</span></a>
        <span style="font-size: 14px; font-weight: 600;">Doctor Portal</span>
    </header>

    <aside class="sidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($doctor_data['name'], 0, 1)) ?></div>
            <h3>Dr. <?= htmlspecialchars($doctor_data['name']) ?></h3>
            <span><?= htmlspecialchars($doctor_data['specialty'] ?: 'Specialist') ?></span>
        </div>

        <ul class="nav-menu">
            <li class="nav-item"><a href="doctor_dashboard.php">📊 Dashboard</a></li>
            <li class="nav-item"><a href="doctor_profile_setting.php" class="active">⚙️ Profile Settings</a></li>
            <li class="nav-item"><a href="logout.php">🚪 Log Out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="panel">
            <h2 style="font-size: 20px; color: #0f172a; font-weight: 700; margin-bottom: 20px;">⚙️ Doctor Profile & Specialty</h2>
            
            <?php if (!empty($success_msg)): ?>
                <div class="alert-success"><?= $success_msg ?></div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <div class="alert-error"><?= $error_msg ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($doctor_data['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Medical Specialty (e.g. Cardiologist, Neurologist)</label>
                    <input type="text" name="specialty" value="<?= htmlspecialchars($doctor_data['specialty']) ?>" placeholder="e.g. Cardiologist" required>
                </div>
                <div class="form-group">
                    <label>Consultation Fee (৳ Taka)</label>
                    <input type="number" step="0.01" name="consultation_fee" value="<?= htmlspecialchars($doctor_data['consultation_fee'] ?? 1000.00) ?>" required>
                </div>
                <button type="submit" class="btn-save">Save Changes</button>
            </form>
        </div>
    </main>
</body>
</html>
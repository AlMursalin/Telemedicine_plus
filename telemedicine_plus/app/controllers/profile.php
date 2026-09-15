<?php
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'name' => 'Md Al mursalin',
        'email' => 'mdalmursalin1234@gmail.com',
        'phone' => '+8801700000000',
        'address' => 'Dhaka, Bangladesh'
    ];

}

$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);
    $new_phone = trim($_POST['phone']);
    $new_address = trim($_POST['address']);

    if (!empty($new_name) && !empty($new_email)) {
        $_SESSION['user']['name'] = $new_name;
        $_SESSION['user']['email'] = $new_email;
        $_SESSION['user']['phone'] = $new_phone;
        $_SESSION['user']['address'] = $new_address;
        $success_message = "Profile updated successfully!";
    }
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | Telemedicine++</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; min-height: 100vh; }
        
        .top-navbar { position: fixed; top: 0; left: 0; right: 0; height: 65px; background: #1e40af; color: white; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .top-logo { font-size: 20px; font-weight: bold; color: white; text-decoration: none; }
        .top-nav-links { display: flex; gap: 25px; align-items: center; list-style: none; font-size: 14px; font-weight: 500; }
        .top-nav-links a { color: #e2e8f0; text-decoration: none; transition: color 0.2s; }
        .top-nav-links a:hover { color: #ffffff; }

        .sidebar { width: 260px; background: #ffffff; border-right: 1px solid #e2e8f0; position: fixed; top: 65px; bottom: 0; left: 0; padding: 25px 15px; display: flex; flex-direction: column; z-index: 90; }
        .sidebar-profile { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; margin-bottom: 20px; }
        .sidebar-avatar { width: 65px; height: 65px; background: #dbeafe; color: #1d4ed8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 10px; font-weight: bold; }
        .sidebar-profile h3 { font-size: 15px; color: #1e293b; font-weight: 700; margin-bottom: 2px; }
        .sidebar-profile span { font-size: 12px; color: #3b82f6; font-weight: 600; text-transform: uppercase; }

        .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 5px; }
        .nav-item a { display: flex; align-items: center; gap: 12px; padding: 11px 15px; border-radius: 8px; color: #64748b; text-decoration: none; font-size: 13.5px; font-weight: 500; transition: all 0.2s; }
        .nav-item a:hover, .nav-item a.active { background: #eff6ff; color: #1d4ed8; font-weight: 600; }
        
        .sidebar-divider { height: 1px; background: #f1f5f9; margin: 15px 0; }

        .main-content { margin-top: 65px; margin-left: 260px; flex: 1; padding: 30px; background: #f8fafc; min-height: calc(100vh - 65px); }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .page-header h1 { font-size: 24px; color: #0f172a; font-weight: 800; }
        
        .btn-back { background: #e2e8f0; color: #334155; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-size: 13.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
        .btn-back:hover { background: #cbd5e1; }

        .form-card { background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 30px; max-width: 700px; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        .alert-success { background: #dcfce7; color: #15803d; padding: 12px 15px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px; font-weight: 600; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 13px; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; }
        .form-group input { width: 100%; padding: 12px 15px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; color: #1e293b; outline: none; transition: border-color 0.2s; }
        .form-group input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }

        .btn-save { background: #2563eb; color: white; border: none; padding: 12px 25px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-save:hover { background: #1d4ed8; }
    </style>
</head>
<body>

    <header class="top-navbar">
        <a href="index.php" class="top-logo">Telemedicine++</a>
        <ul class="top-nav-links">
            <li><a href="index.php">🏠 Home</a></li>
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="logout.php" style="color: #fca5a5;">Log Out (<?= htmlspecialchars($user['name']) ?>)</a></li>
        </ul>
    </header>

    <aside class="sidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <h3><?= htmlspecialchars($user['name']) ?></h3>
            <span>Patient</span>
        </div>

        <ul class="nav-menu">
            <li class="nav-item"><a href="dashboard.php">📊 Dashboard</a></li>
            <li class="nav-item"><a href="book_appointment.php">📅 Track Schedule</a></li>
            <li class="nav-item"><a href="profile.php" class="active">👤 Edit Profile</a></li>
            <li class="nav-item"><a href="book_appointment.php">📋 Appointments</a></li>
            <li class="nav-item"><a href="meeting.php">📹 Live Appointment</a></li>
            <li class="nav-item"><a href="medicines.php">💳 Make Payment</a></li>
            
            <div class="sidebar-divider"></div>

            <li class="nav-item"><a href="#">📁 Medical Records</a></li>
            <li class="nav-item"><a href="#">✉️ Messages</a></li>
            <li class="nav-item"><a href="#">🔔 Notifications</a></li>
            
            <div class="sidebar-divider"></div>

            <li class="nav-item"><a href="#">⚙️ Settings</a></li>
            <li class="nav-item"><a href="#">❓ Help & Support</a></li>
        </ul>
    </aside>

    <main class="main-content">
        
        <div class="page-header">
            <h1>Edit Patient Profile</h1>
            <a href="dashboard.php" class="btn-back">&larr; Back to Dashboard</a>
        </div>

        <div class="form-card">
            <?php if (!empty($success_message)): ?>
                <div class="alert-success"><?= $success_message ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Enter phone number">
                </div>

                <div class="form-group">
                    <label>Residential Address</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Enter address">
                </div>

                <button type="submit" name="update_profile" class="btn-save">💾 Save Changes</button>
            </form>
        </div>

    </main>

</body>
</html>
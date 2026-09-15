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
    <title>Live Consultation | Telemedicine++</title>
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

        .video-container { background: #0f172a; border-radius: 14px; height: 450px; display: flex; align-items: center; justify-content: center; color: white; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .video-placeholder { text-align: center; }
        .video-placeholder h3 { font-size: 20px; margin-bottom: 8px; }
        .video-placeholder p { color: #94a3b8; font-size: 14px; }
        .controls-bar { display: flex; justify-content: center; gap: 15px; margin-top: 20px; }
        .btn-control { background: #ffffff; border: 1px solid #cbd5e1; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 14px; color: #334155; }
        .btn-control:hover { background: #f1f5f9; }
        .btn-end { background: #dc2626; color: white; border: none; }
        .btn-end:hover { background: #b91c1c; }
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
            <li class="nav-item"><a href="index.php?route=patient_appointments">📅 Track Schedule</a></li>
            <li class="nav-item"><a href="index.php?route=profile">👤 Edit Profile</a></li>
            <li class="nav-item"><a href="index.php?route=patient_appointments">📋 Appointments</a></li>
            <li class="nav-item"><a href="index.php?route=meeting" class="active">📹 Live Appointment</a></li>
            <li class="nav-item"><a href="index.php?route=medicines">💳 Make Payment</a></li>
            <div class="sidebar-divider"></div>
            <li class="nav-item"><a href="index.php?route=logout">🚪 Log Out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Live Consultation Room</h1>
            <p>Connect securely with your assigned medical specialist.</p>
        </div>

        <div class="video-container">
            <div class="video-placeholder">
                <h3>Connecting to Secure Video Call...</h3>
                <p>Please ensure your camera and microphone permissions are enabled.</p>
            </div>
        </div>

        <div class="controls-bar">
            <button class="btn-control">Mute Mic</button>
            <button class="btn-control">Stop Camera</button>
            <a href="index.php?route=patient_dashboard" class="btn-control btn-end" style="text-decoration: none; display: inline-block;">Leave Call</a>
        </div>
    </main>

</body>
</html>
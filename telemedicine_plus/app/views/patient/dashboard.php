<?php
require_once 'db.php';


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user'];

$doctors = [];
try {
    $stmt = $db->prepare("SELECT id, name, specialty, consultation_fee FROM users WHERE role = 'doctor' ORDER BY name ASC");
    $stmt->execute();
    $doctors = $stmt->fetchAll();
} catch (Exception $e) {
    $doctors = [];
}

$sitters = [];
try {
    $stmt_s = $db->prepare("SELECT id, name, specialty, hourly_rate FROM users WHERE role = 'sitter' AND availability = 'Available' ORDER BY name ASC");
    $stmt_s->execute();
    $sitters = $stmt_s->fetchAll();
} catch (Exception $e) {
    $sitters = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard | Telemedicine++</title>
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
        
        .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
        .welcome-box h1 { font-size: 26px; color: #0f172a; font-weight: 800; margin-bottom: 4px; }
        .welcome-box p { font-size: 14px; color: #64748b; }
        
        .datetime-widget { background: white; padding: 12px 20px; border-radius: 12px; display: flex; gap: 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .dt-item { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #475569; }
        .dt-item strong { color: #0f172a; display: block; font-size: 12px; }

        .action-cards-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 25px; }
        .action-card { background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px 15px; text-align: center; text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.01); display: flex; flex-direction: column; align-items: center; }
        .action-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-color: #cbd5e1; }
        .action-icon { width: 50px; height: 50px; background: #f0fdf4; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px; }
        .action-card h3 { font-size: 14.5px; color: #1e293b; font-weight: 700; margin-bottom: 6px; }
        .action-card p { font-size: 11.5px; color: #64748b; line-height: 1.4; margin-bottom: 15px; }
        .action-arrow { margin-top: auto; color: #94a3b8; font-size: 16px; transition: transform 0.2s; }
        .action-card:hover .action-arrow { transform: translateX(4px); color: #2563eb; }

        .directory-tabs { display: flex; gap: 12px; margin-bottom: 20px; }
        .tab-btn { padding: 11px 22px; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; border: 1px solid #cbd5e1; transition: all 0.2s; background: white; color: #475569; display: flex; align-items: center; gap: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .tab-btn.active-doc { background: #2563eb; color: white; border-color: #2563eb; }
        .tab-btn.active-sitter { background: #059669; color: white; border-color: #059669; }

        .doctors-section { margin-bottom: 25px; }
        .doctor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
        
        .doctor-card { 
            background: white; 
            border: 1px solid #e2e8f0; 
            border-radius: 16px; 
            padding: 24px 20px; 
            text-align: center; 
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01); 
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .doctor-card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.06); border-color: #cbd5e1; }
        
        .doc-avatar-box { 
            width: 60px; 
            height: 60px; 
            background: #e0f2fe; 
            color: #0284c7; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 24px; 
            font-weight: 800; 
            margin: 0 auto 14px; 
        }
        
        .doc-name { font-size: 16.5px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .doc-specialty { font-size: 13.5px; color: #2563eb; font-weight: 600; margin-bottom: 8px; display: block; }
        
        .doc-id-badge { 
            font-size: 11.5px; 
            background: #f1f5f9; 
            color: #475569; 
            padding: 3px 10px; 
            border-radius: 6px; 
            display: inline-block; 
            margin-bottom: 12px; 
            font-weight: 600; 
        }
        
        .doc-fee { font-size: 14px; color: #475569; margin-bottom: 20px; }
        .doc-fee strong { color: #0f172a; font-weight: 700; }
        
        .btn-book-doc { 
            background: #2563eb; 
            color: white; 
            width: 100%; 
            padding: 11px; 
            border-radius: 10px; 
            text-decoration: none; 
            font-size: 13.5px; 
            font-weight: 700; 
            transition: background 0.2s; 
            margin-top: auto;
            box-shadow: 0 2px 4px rgba(37,99,235,0.2);
        }
        .btn-book-doc:hover { background: #1d4ed8; }

        .btn-book-sitter { 
            background: #059669; 
            color: white; 
            width: 100%; 
            padding: 11px; 
            border-radius: 10px; 
            text-decoration: none; 
            font-size: 13.5px; 
            font-weight: 700; 
            transition: background 0.2s; 
            margin-top: auto;
            box-shadow: 0 2px 4px rgba(5,150,105,0.2);
        }
        .btn-book-sitter:hover { background: #047857; }
    </style>
    <script>
        function switchDirectoryTab(type) {
            const docSection = document.getElementById('doctors-directory-box');
            const sitterSection = document.getElementById('sitters-directory-box');
            const btnDoc = document.getElementById('tab-doc-btn');
            const btnSitter = document.getElementById('tab-sitter-btn');

            if (type === 'doctors') {
                docSection.style.display = 'grid';
                sitterSection.style.display = 'none';
                btnDoc.className = 'tab-btn active-doc';
                btnSitter.className = 'tab-btn';
            } else {
                docSection.style.display = 'none';
                sitterSection.style.display = 'grid';
                btnSitter.className = 'tab-btn active-sitter';
                btnDoc.className = 'tab-btn';
            }
        }

        function updateLiveClock() {
            const now = new Date();
            const dateOptions = { month: 'short', day: 'numeric', year: 'numeric' };
            document.getElementById('live-date').innerText = now.toLocaleDateString('en-US', dateOptions);
            const timeOptions = { hour: '2-digit', minute: '2-digit', hour12: true };
            document.getElementById('live-time').innerText = now.toLocaleTimeString('en-US', timeOptions);
        }
        setInterval(updateLiveClock, 1000);
        window.onload = updateLiveClock;
    </script>
</head>
<body>

    <header class="top-navbar">
        <a href="index.php" class="top-logo"><span>Telemedicine++</span></a>
        <ul class="top-nav-links">
            <li><a href="index.php">🏠 Home</a></li>
            <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
            <li><a href="logout.php" style="color: #fca5a5;">Log Out (<?= htmlspecialchars($user['name']) ?>)</a></li>
        </ul>
    </header>

    <aside class="sidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
            <h3><?= htmlspecialchars($user['name']) ?></h3>
            <span>Patient</span>
        </div>

        <ul class="nav-menu">
            <li class="nav-item"><a href="dashboard.php" class="active">📊 Dashboard</a></li>
            <li class="nav-item"><a href="patient_appointments.php">📅 Track Schedule</a></li>
            <li class="nav-item"><a href="profile.php">👤 Edit Profile</a></li>
            <li class="nav-item"><a href="patient_appointments.php">📋 Appointments</a></li>
            <li class="nav-item"><a href="meeting.php">📹 Live Appointment</a></li>
            <li class="nav-item"><a href="medicines.php">💳 Make Payment</a></li>
            <div class="sidebar-divider"></div>
            <li class="nav-item"><a href="logout.php">🚪 Log Out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="dashboard-header">
            <div class="welcome-box">
                <h1>Hello, <?= htmlspecialchars($user['name']) ?>! 👋</h1>
                <p>Welcome back! Here's your health overview.</p>
            </div>
            <div class="datetime-widget">
                <div class="dt-item">
                    <span style="font-size: 20px;">📅</span>
                    <div>
                        <strong>Today</strong>
                        <span id="live-date">Loading...</span>
                    </div>
                </div>
                <div style="width: 1px; background: #e2e8f0;"></div>
                <div class="dt-item">
                    <span style="font-size: 20px;">🕒</span>
                    <div>
                        <strong>Time</strong>
                        <span id="live-time">Loading...</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="action-cards-grid">
            <a href="patient_appointments.php" class="action-card">
                <div class="action-icon" style="background: #eff6ff; color: #2563eb;">📅</div>
                <h3>Track Schedule</h3>
                <p>View your upcoming dates and events.</p>
                <div class="action-arrow">&rarr;</div>
            </a>
            <a href="profile.php" class="action-card">
                <div class="action-icon" style="background: #f5f3ff; color: #7c3aed;">👤</div>
                <h3>Edit Profile</h3>
                <p>Update your personal information.</p>
                <div class="action-arrow">&rarr;</div>
            </a>
            <a href="patient_appointments.php" class="action-card">
                <div class="action-icon" style="background: #fff7ed; color: #ea580c;">📋</div>
                <h3>Appointments</h3>
                <p>View or update your appointments.</p>
                <div class="action-arrow">&rarr;</div>
            </a>
            <a href="meeting.php" class="action-card">
                <div class="action-icon" style="background: #f0fdf4; color: #16a34a;">📹</div>
                <h3>Live Appointment</h3>
                <p>Join a WebRTC call with your doctor.</p>
                <div class="action-arrow">&rarr;</div>
            </a>
            <a href="medicines.php" class="action-card">
                <div class="action-icon" style="background: #fdf2f8; color: #db2777;">💳</div>
                <h3>Make Payment</h3>
                <p>Pay your bills and view payment history.</p>
                <div class="action-arrow">&rarr;</div>
            </a>
        </div>

        <div class="doctors-section">
            <div class="directory-tabs">
                <button id="tab-doc-btn" class="tab-btn active-doc" onclick="switchDirectoryTab('doctors')">🩺 Browse Doctors & Specialists</button>
                <button id="tab-sitter-btn" class="tab-btn" onclick="switchDirectoryTab('sitters')">🛏️ Browse Hospital Sitters</button>
            </div>

            <div id="doctors-directory-box" class="doctor-grid">
                <?php if (empty($doctors)): ?>
                    <p style="color: #64748b; font-size: 14px; grid-column: 1/-1;">No doctors have registered on the portal yet.</p>
                <?php else: ?>
                    <?php foreach ($doctors as $doc): ?>
                        <div class="doctor-card">
                            <div class="doc-avatar-box"><?= strtoupper(substr($doc['name'], 0, 1)) ?></div>
                            <div class="doc-name"><?= htmlspecialchars($doc['name']) ?></div>
                            <span class="doc-specialty"><?= htmlspecialchars($doc['specialty']) ?></span>
                            <div class="doc-id-badge">Doctor ID: #<?= $doc['id'] ?></div>
                            <div class="doc-fee">Fee: <strong>৳<?= number_format($doc['consultation_fee'] ?? 1000, 2) ?></strong></div>
                            <a href="book_appointment.php?doctor_id=<?= $doc['id'] ?>" class="btn-book-doc">Book Appointment &rarr;</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div id="sitters-directory-box" class="doctor-grid" style="display: none;">
                <?php if (empty($sitters)): ?>
                    <p style="color: #64748b; font-size: 14px; grid-column: 1/-1;">No available hospital sitters at the moment.</p>
                <?php else: ?>
                    <?php foreach ($sitters as $sitter): ?>
                        <div class="doctor-card" style="border-top: 4px solid #059669;">
                            <div class="doc-avatar-box" style="background: #d1fae5; color: #059669;"><?= strtoupper(substr($sitter['name'], 0, 1)) ?></div>
                            <div class="doc-name"><?= htmlspecialchars($sitter['name']) ?></div>
                            <span class="doc-specialty" style="color: #059669;"><?= htmlspecialchars($sitter['specialty']) ?></span>
                            <div class="doc-id-badge">Sitter ID: #<?= $sitter['id'] ?></div>
                            <div class="doc-fee">Rate: <strong>৳<?= number_format($sitter['hourly_rate'] ?? 500, 2) ?> / hr</strong></div>
                            <a href="book_sitter.php?sitter_id=<?= $sitter['id'] ?>" class="btn-book-sitter">Request Sitter &rarr;</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
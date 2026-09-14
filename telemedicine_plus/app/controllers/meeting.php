<?php
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user = $_SESSION['user'];

$dashboard_url = 'dashboard.php';
if ($user['role'] === 'doctor') {
    $dashboard_url = 'doctor_dashboard.php';
} elseif ($user['role'] === 'sitter') {
    $dashboard_url = 'sitter_dashboard.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Appointment | Telemedicine++</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; min-height: 100vh; display: flex; flex-direction: column; }
        
        .top-navbar { height: 75px; background: #ffffff; color: #1e293b; display: flex; justify-content: space-between; align-items: center; padding: 0 50px; border-bottom: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .top-logo { font-size: 20px; font-weight: 800; color: #0284c7; text-decoration: none; display: flex; align-items: center; gap: 8px; }
        .top-nav-links { display: flex; gap: 30px; align-items: center; list-style: none; font-size: 14px; font-weight: 600; color: #64748b; }
        .top-nav-links a { color: #64748b; text-decoration: none; transition: color 0.2s; }
        .top-nav-links a:hover { color: #0284c7; }

        .main-container { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; }
        
        .datetime-widget { background: white; border: 1px solid #e2e8f0; padding: 12px 25px; border-radius: 14px; display: flex; gap: 25px; box-shadow: 0 2px 6px rgba(0,0,0,0.02); margin-bottom: 25px; align-items: center; }
        .dt-item { display: flex; align-items: center; gap: 12px; font-size: 13.5px; color: #475569; }
        .dt-item strong { color: #0f172a; display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        .dt-item span { font-weight: 600; color: #1e293b; font-size: 14px; }

        .not-found-card { background: white; border: 1px solid #e2e8f0; border-radius: 20px; padding: 50px 40px; text-align: center; width: 100%; max-width: 650px; box-shadow: 0 10px 25px rgba(0,0,0,0.03); margin-bottom: 30px; }
        
        .icon-box { position: relative; width: 90px; height: 90px; background: #eff6ff; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 42px; margin: 0 auto 25px; }
        .badge-close { position: absolute; bottom: -4px; right: -4px; width: 28px; height: 28px; background: #ef4444; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; border: 2px solid white; }

        .not-found-card h2 { font-size: 28px; font-weight: 800; color: #0f172a; margin-bottom: 10px; }
        .not-found-card h2 span { color: #2563eb; }
        .not-found-card p { font-size: 14.5px; color: #64748b; line-height: 1.5; margin-bottom: 30px; max-width: 450px; margin-left: auto; margin-right: auto; }

        .button-group { display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
        
        .btn-book { background: #2563eb; color: white; padding: 12px 28px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 700; transition: background 0.2s, transform 0.2s; box-shadow: 0 4px 12px rgba(37,99,235,0.2); display: inline-flex; align-items: center; gap: 8px; }
        .btn-book:hover { background: #1d4ed8; transform: translateY(-2px); }

        .btn-dashboard { background: #f1f5f9; color: #334155; padding: 12px 28px; border-radius: 12px; text-decoration: none; font-size: 14px; font-weight: 700; transition: background 0.2s, transform 0.2s; border: 1px solid #cbd5e1; display: inline-flex; align-items: center; gap: 8px; }
        .btn-dashboard:hover { background: #e2e8f0; transform: translateY(-2px); }

        .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; width: 100%; max-width: 650px; }
        .feature-card { background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; gap: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.01); }
        .feat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; background: #f0fdf4; }
        .feat-info h4 { font-size: 13.5px; font-weight: 700; color: #1e293b; margin-bottom: 2px; }
        .feat-info p { font-size: 11.5px; color: #64748b; }

        .footer-text { text-align: center; margin-top: 30px; font-size: 12.5px; color: #94a3b8; }
    </style>
    <script>
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
        <a href="index.php" class="top-logo">§ TeleMedicine ++</a>
        <ul class="top-nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="dashboard.php">Doctors</a></li>
            <li><a href="patient_appointments.php">Appointments</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="<?= $dashboard_url ?>" style="color: #2563eb; font-weight: 700;">👤 <?= htmlspecialchars($user['name']) ?></a></li>
        </ul>
    </header>

    <main class="main-container">
        
        <div class="datetime-widget">
            <div class="dt-item">
                <span style="font-size: 22px;">📅</span>
                <div>
                    <strong>Today</strong>
                    <span id="live-date">Loading...</span>
                </div>
            </div>
            <div style="width: 1px; background: #e2e8f0;"></div>
            <div class="dt-item">
                <span style="font-size: 22px;">🕒</span>
                <div>
                    <strong>Time</strong>
                    <span id="live-time">Loading...</span>
                </div>
            </div>
        </div>

        <div class="not-found-card">
            <div class="icon-box">
                📅
                <div class="badge-close">✕</div>
            </div>
            <h2>Appointment <span>Not Found</span></h2>
            <p>We couldn't find any upcoming appointment with this information. Don't worry! You can easily book a new appointment or return to your portal.</p>
            
            <div class="button-group">
                <a href="dashboard.php" class="btn-book">📅 Book Here →</a>
                <a href="<?= $dashboard_url ?>" class="btn-dashboard">🏠 Back to Dashboard</a>
            </div>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feat-icon" style="background: #eff6ff; color: #2563eb;">👨‍⚕️</div>
                <div class="feat-info">
                    <h4>Expert Doctors</h4>
                    <p>Consult with the best</p>
                </div>
            </div>
            <div class="feature-card">
                <div class="feat-icon" style="background: #f0fdf4; color: #16a34a;">🛡️</div>
                <div class="feat-info">
                    <h4>Safe & Secure</h4>
                    <p>Your data is protected</p>
                </div>
            </div>
            <div class="feature-card">
                <div class="feat-icon" style="background: #fff7ed; color: #ea580c;">⚡</div>
                <div class="feat-info">
                    <h4>Quick & Easy</h4>
                    <p>Book in minutes</p>
                </div>
            </div>
        </div>

        <div class="footer-text">
            Stay Healthy, Stay Happy ❤️
        </div>
    </main>

</body>
</html>
<?php
if (!isset($doctor) && isset($_SESSION['user'])) {
    $doctor = $_SESSION['user'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records | Telemedicine++</title>
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
        .sidebar-avatar { width: 65px; height: 65px; background: #e0e7ff; color: #3730a3; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 10px; font-weight: bold; }
        .sidebar-profile h3 { font-size: 15px; color: #1e293b; font-weight: 700; margin-bottom: 2px; }
        .sidebar-profile span { font-size: 12px; color: #4338ca; font-weight: 600; text-transform: uppercase; }

        .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 5px; }
        .nav-item a { display: flex; align-items: center; gap: 12px; padding: 11px 15px; border-radius: 8px; color: #64748b; text-decoration: none; font-size: 13.5px; font-weight: 500; transition: all 0.2s; }
        .nav-item a:hover, .nav-item a.active { background: #eef2ff; color: #4338ca; font-weight: 600; }
        .sidebar-divider { height: 1px; background: #f1f5f9; margin: 15px 0; }

        .main-content { margin-top: 65px; margin-left: 260px; flex: 1; padding: 30px; background: #f8fafc; min-height: calc(100vh - 65px); }
        .page-header { margin-bottom: 25px; }
        .page-header h1 { font-size: 26px; color: #0f172a; font-weight: 800; margin-bottom: 4px; }
        .page-header p { font-size: 14px; color: #64748b; }

        .card { background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        table { width: 100%; border-collapse: collapse; text-align: left; margin-top: 10px; }
        th { background: #f8fafc; padding: 12px 15px; font-size: 13px; font-weight: 700; color: #475569; border-bottom: 1px solid #e2e8f0; }
        td { padding: 14px 15px; font-size: 13.5px; color: #1e293b; border-bottom: 1px solid #f1f5f9; }
        tr:hover td { background: #f8fafc; }
        
        .badge { padding: 5px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 600; display: inline-block; background: #dcfce7; color: #166534; }
        .btn-action { background: #4338ca; color: white; padding: 7px 14px; border-radius: 6px; text-decoration: none; font-size: 12.5px; font-weight: 600; transition: background 0.2s; display: inline-block; }
        .btn-action:hover { background: #3730a3; }
    </style>
</head>
<body>

    <header class="top-navbar">
        <a href="index.php?route=home" class="top-logo"><span>Telemedicine++</span></a>
        <ul class="top-nav-links">
            <li><a href="index.php?route=home">🏠 Home</a></li>
            <li><a href="index.php?route=doctor_dashboard">📊 Dashboard</a></li>
            <li><a href="index.php?route=logout" style="color: #fca5a5;">Log Out (Dr. <?= htmlspecialchars($doctor['name'] ?? 'Doctor') ?>)</a></li>
        </ul>
    </header>

    <aside class="sidebar">
        <div class="sidebar-profile">
            <div class="sidebar-avatar"><?= strtoupper(substr($doctor['name'] ?? 'D', 0, 1)) ?></div>
            <h3>Dr. <?= htmlspecialchars($doctor['name'] ?? 'Doctor') ?></h3>
            <span><?= htmlspecialchars($doctor['specialty'] ?? 'Specialist') ?></span>
        </div>

        <ul class="nav-menu">
            <li class="nav-item"><a href="index.php?route=doctor_dashboard">📊 Dashboard</a></li>
            <li class="nav-item"><a href="index.php?route=doctor_appointments">📅 Appointments</a></li>
            <li class="nav-item"><a href="index.php?route=medical_records" class="active">📋 Medical Records</a></li>
            <div class="sidebar-divider"></div>
            <li class="nav-item"><a href="index.php?route=logout">🚪 Log Out</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Completed Medical Records</h1>
            <p>Archive of completed consultations, patient prescriptions, and medical advice.</p>
        </div>

        <div class="card">
            <?php if (empty($records)): ?>
                <p style="color: #64748b; font-size: 14px; text-align: center; padding: 20px;">No completed medical records found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Date</th>
                            <th>Condition</th>
                            <th>Prescription</th>
                            <th>Advice</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $rec): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($rec['patient_name']) ?></strong></td>
                                <td><?= htmlspecialchars($rec['appointment_date']) ?></td>
                                <td><?= htmlspecialchars($rec['patient_condition'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($rec['prescription'] ?? 'None prescribed') ?></td>
                                <td><?= htmlspecialchars($rec['doctor_advice'] ?? 'None given') ?></td>
                                <td><span class="badge"><?= htmlspecialchars($rec['status']) ?></span></td>
                                <td>
                                    <a href="index.php?route=manage_appointment&id=<?= $rec['id'] ?>" class="btn-action">View &rarr;</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
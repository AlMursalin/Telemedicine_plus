<?php
require_once __DIR__ . '/../../../config/db.php';



$doc_stmt = $db->prepare("SELECT id, name, specialty, consultation_fee FROM users WHERE role = 'doctor' ORDER BY name ASC");
$doc_stmt->execute();
$doctors = $doc_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Find Doctors | Telemedicine++</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; min-height: 100vh; }
        .top-navbar { position: fixed; top: 0; left: 0; right: 0; height: 65px; background: #2563eb; color: white; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .top-logo { font-size: 20px; font-weight: bold; color: white; text-decoration: none; }
        .main-content { margin-top: 65px; flex: 1; padding: 40px; }
        .header-title { font-size: 24px; color: #0f172a; font-weight: 800; margin-bottom: 25px; }
        .doctor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
        .doctor-card { background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px 20px; text-align: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); display: flex; flex-direction: column; align-items: center; }
        .doc-avatar-box { width: 60px; height: 60px; background: #e0f2fe; color: #0284c7; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; margin-bottom: 14px; }
        .doc-name { font-size: 16.5px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .doc-specialty { font-size: 13.5px; color: #2563eb; font-weight: 600; margin-bottom: 8px; display: block; }
        .doc-fee { font-size: 14px; color: #475569; margin-bottom: 20px; }
        .doc-fee strong { color: #0f172a; }
        .btn-book-doc { background: #2563eb; color: white; width: 100%; padding: 11px; border-radius: 10px; text-decoration: none; font-size: 13.5px; font-weight: 700; margin-top: auto; }
        .btn-book-doc:hover { background: #1d4ed8; }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #2563eb; text-decoration: none; font-weight: 600; font-size: 14px; }
    </style>
</head>
<body>
    <header class="top-navbar"><a href="index.php?route=home" class="top-logo">✚ Telemedicine++</a></header>
    <main class="main-content">
        <a href="index.php?route=patient_dashboard" class="btn-back">&larr; Back to Dashboard</a>
        <h1 class="header-title">🩺 Verified Doctors & Specialists Directory</h1>
        <div class="doctor-grid">
            <?php if (empty($doctors)): ?>
                <p style="color: #64748b; font-size: 14px; grid-column: 1/-1;">No doctors registered yet.</p>
            <?php else: ?>
                <?php foreach ($doctors as $doc): ?>
                    <div class="doctor-card">
                        <div class="doc-avatar-box"><?= strtoupper(substr($doc['name'], 0, 1)) ?></div>
                        <div class="doc-name"><?= htmlspecialchars($doc['name']) ?></div>
                        <span class="doc-specialty"><?= htmlspecialchars($doc['specialty']) ?></span>
                        <div class="doc-fee">Consultation Fee: <strong>৳<?= number_format($doc['consultation_fee'] ?? 1000, 2) ?></strong></div>
                        <a href="index.php?route=book_appointment&doctor_id=<?= $doc['id'] ?>" class="btn-book-doc">Book Appointment &rarr;</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
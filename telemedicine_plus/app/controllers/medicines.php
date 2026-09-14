<?php
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    $_SESSION['user'] = [
        'name' => 'Mursalin',
        'email' => 'mdalmursalin1234@gmail.com'
    ];
}
$user = $_SESSION['user'];

if (!isset($_SESSION['payment_items'])) {
    $_SESSION['payment_items'] = [
        'doctor' => ['name' => 'Doctor Consultation Fee (Dr. Ahmed Hossain)', 'price' => 1500, 'active' => true],
        'medicine' => ['name' => 'Medicine Store Order (Prescribed Medications)', 'price' => 750, 'active' => true],
        'meeting' => ['name' => 'Live Video Session Connection Pass', 'price' => 500, 'active' => true]
    ];
}

$available_catalog = [
    'lab_blood' => ['name' => 'Complete Blood Count (CBC) Lab Test', 'price' => 800],
    'lab_diabetes' => ['name' => 'HbA1c Diabetes Blood Screening', 'price' => 1200],
    'dental_check' => ['name' => 'Dental Specialist Consultation', 'price' => 1000],
    'physio_session' => ['name' => 'Physical Therapy & Rehabilitation Session', 'price' => 1500],
    'covid_test' => ['name' => 'COVID-19 RT-PCR Home Test Kit', 'price' => 1500],
    'cardio_ecg' => ['name' => 'ECG Heart Checkup', 'price' => 2000]
];

if (isset($_GET['add_catalog'])) {
    $cat_key = $_GET['add_catalog'];
    if (isset($available_catalog[$cat_key])) {
        $unique_id = $cat_key . '_' . time();
        $_SESSION['payment_items'][$unique_id] = [
            'name' => $available_catalog[$cat_key]['name'],
            'price' => $available_catalog[$cat_key]['price'],
            'active' => true
        ];
    }
    header("Location: medicines.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_custom_item'])) {
    $item_name = trim($_POST['custom_item_name']);
    $item_price = floatval($_POST['custom_item_price']);
    
    if (!empty($item_name) && $item_price > 0) {
        $new_key = 'custom_' . time();
        $_SESSION['payment_items'][$new_key] = [
            'name' => $item_name,
            'price' => $item_price,
            'active' => true
        ];
    }
    header("Location: medicines.php");
    exit;
}

if (isset($_GET['toggle'])) {
    $key = $_GET['toggle'];
    if (isset($_SESSION['payment_items'][$key])) {
        $_SESSION['payment_items'][$key]['active'] = !$_SESSION['payment_items'][$key]['active'];
    }
    header("Location: medicines.php");
    exit;
}

$success_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_all_now'])) {
    $method = $_POST['payment_method'];
    $current_total = 0;
    foreach ($_SESSION['payment_items'] as $item) {
        if ($item['active']) $current_total += $item['price'];
    }

    if ($current_total > 0) {
        $success_msg = "Successfully processed single combined payment of ৳" . number_format($current_total, 2) . " via " . strtoupper($method) . "!";
    } else {
        $success_msg = "Please select at least one item to proceed with payment.";
    }
}

$total_combined_amount = 0;
foreach ($_SESSION['payment_items'] as $item) {
    if ($item['active']) {
        $total_combined_amount += $item['price'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment | Telemedicine++</title>
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

        .alert-success { background: #dcfce7; color: #15803d; padding: 14px 20px; border-radius: 10px; font-size: 14.5px; margin-bottom: 25px; font-weight: 600; border: 1px solid #bbf7d0; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }

        .card-box { background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 30px; max-width: 750px; box-shadow: 0 4px 12px rgba(0,0,0,0.02); margin-bottom: 25px; }
        .box-title { font-size: 17px; color: #1e293b; font-weight: 700; margin-bottom: 15px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; }
        
        .catalog-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 10px; }
        .catalog-item { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 15px; display: flex; justify-content: space-between; align-items: center; }
        .catalog-info h4 { font-size: 13px; color: #1e293b; font-weight: 700; margin-bottom: 2px; }
        .catalog-info span { font-size: 13px; color: #2563eb; font-weight: 800; }
        .btn-add-catalog { background: #e0f2fe; color: #0284c7; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; text-decoration: none; transition: background 0.2s; white-space: nowrap; }
        .btn-add-catalog:hover { background: #bae6fd; }

        .itemized-list { margin-bottom: 25px; display: flex; flex-direction: column; gap: 12px; }
        .item-row { display: flex; justify-content: space-between; align-items: center; font-size: 14px; color: #475569; padding: 10px 12px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; }
        .item-row.cancelled { opacity: 0.5; background: #f1f5f9; text-decoration: line-through; }
        .item-details { display: flex; align-items: center; gap: 10px; }
        
        .btn-toggle { padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .btn-toggle.cancel { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
        .btn-toggle.cancel:hover { background: #fecaca; }
        .btn-toggle.add { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; text-decoration: none; }
        .btn-toggle.add:hover { background: #bbf7d0; }

        .total-row { display: flex; justify-content: space-between; font-size: 18px; color: #0f172a; font-weight: 800; padding-top: 5px; margin-bottom: 25px; border-top: 2px solid #f1f5f9; padding-top: 15px; }
        .total-row span:last-child { color: #2563eb; font-size: 22px; }

        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; }
        .form-group select, .form-group input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; background: #fff; }

        .btn-pay-all { background: #10b981; color: white; border: none; width: 100%; padding: 14px; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: background 0.2s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
        .btn-pay-all:hover { background: #059669; }

        .btn-custom { background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
        .btn-custom:hover { background: #1d4ed8; }
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
            <div class="sidebar-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
            <h3><?= htmlspecialchars($user['name']) ?></h3>
            <span>PATIENT</span>
        </div>

        <ul class="nav-menu">
            <li class="nav-item"><a href="dashboard.php">📊 Dashboard</a></li>
            <li class="nav-item"><a href="book_appointment.php">📅 Track Schedule</a></li>
            <li class="nav-item"><a href="profile.php">👤 Edit Profile</a></li>
            <li class="nav-item"><a href="book_appointment.php">📋 Appointments</a></li>
            <li class="nav-item"><a href="meeting.php">📹 Live Appointment</a></li>
            <li class="nav-item"><a href="medicines.php" class="active">💳 Make Payment</a></li>
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
            <h1>Make Secure Payments</h1>
            <a href="dashboard.php" class="btn-back">&larr; Back to Dashboard</a>
        </div>

        <?php if (!empty($success_msg)): ?>
            <div class="alert-success"><?= $success_msg ?></div>
        <?php endif; ?>

        <div class="card-box">
            <div class="box-title">💡 Available Healthcare Services & Items to Add</div>
            <div class="catalog-grid">
                <?php foreach ($available_catalog as $cat_key => $cat_item): ?>
                    <div class="catalog-item">
                        <div class="catalog-info">
                            <h4><?= htmlspecialchars($cat_item['name']) ?></h4>
                            <span>৳<?= number_format($cat_item['price'], 2) ?></span>
                        </div>
                        <a href="medicines.php?add_catalog=<?= $cat_key ?>" class="btn-add-catalog">+ Add Item</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card-box">
            <div class="box-title">➕ Add Custom Manual Item</div>
            <form method="POST">
                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 2; margin-bottom: 0;">
                        <label>Item Name / Description</label>
                        <input type="text" name="custom_item_name" placeholder="e.g. Special Care Package" required>
                    </div>
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label>Amount (৳)</label>
                        <input type="number" step="0.01" name="custom_item_price" placeholder="e.g. 500" required>
                    </div>
                </div>
                <button type="submit" name="add_custom_item" class="btn-custom" style="margin-top: 15px;">+ Add Custom Item</button>
            </form>
        </div>

        <div class="card-box">
            <div class="box-title">🧾 Combined Billing Checkout Summary</div>
            
            <div class="itemized-list" style="margin-top: 15px;">
                <?php foreach ($_SESSION['payment_items'] as $key => $item): ?>
                    <div class="item-row <?= $item['active'] ? '' : 'cancelled' ?>">
                        <div class="item-details">
                            <span><?= htmlspecialchars($item['name']) ?> — <strong>৳<?= number_format($item['price'], 2) ?></strong></span>
                        </div>
                        <div>
                            <?php if ($item['active']): ?>
                                <a href="medicines.php?toggle=<?= $key ?>" class="btn-toggle cancel">Cancel Item</a>
                            <?php else: ?>
                                <a href="medicines.php?toggle=<?= $key ?>" class="btn-toggle add">+ Add Back</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="total-row">
                <span>Total Combined Payable Amount:</span>
                <span>৳<?= number_format($total_combined_amount, 2) ?></span>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Select Preferred Payment Gateway</label>
                    <select name="payment_method" required>
                        <option value="bkash">bKash Mobile Banking</option>
                        <option value="nagad">Nagad Mobile Pay</option>
                        <option value="card">Visa / Mastercard / Debit Card</option>
                    </select>
                </div>
                <button type="submit" name="pay_all_now" class="btn-pay-all">Pay All Combined (৳<?= number_format($total_combined_amount, 2) ?>) Securely</button>
            </form>
        </div>
    </main>
</body>
</html>
<?php
require_once __DIR__ . '/../../../config/db.php';
if (!isset($_SESSION['user'])) { header("Location: index.php?route=login"); exit; }


$user = $_SESSION['user'];

if (!isset($_SESSION['payment_items'])) {
    $_SESSION['payment_items'] = [
        'doctor' => ['name' => 'Doctor Consultation Fee', 'price' => 1500, 'active' => true],
        'medicine' => ['name' => 'Medicine Store Order', 'price' => 750, 'active' => true]
    ];
}
$total_combined_amount = array_sum(array_column(array_filter($_SESSION['payment_items'], fn($i)=>$i['active']), 'price'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medicine Store & Payments | Telemedicine++</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; color: #333; display: flex; min-height: 100vh; }
        .top-navbar { position: fixed; top: 0; left: 0; right: 0; height: 65px; background: #db2777; color: white; display: flex; justify-content: space-between; align-items: center; padding: 0 30px; z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .top-logo { font-size: 20px; font-weight: bold; color: white; text-decoration: none; }
        .main-content { margin-top: 65px; flex: 1; padding: 40px; display: flex; justify-content: center; }
        .panel { background: white; border: 1px solid #e2e8f0; border-radius: 16px; padding: 35px; width: 100%; max-width: 650px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .panel h2 { font-size: 22px; margin-bottom: 20px; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px; }
        .bill-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9; font-size: 15px; }
        .bill-total { display: flex; justify-content: space-between; padding: 18px 0; font-size: 18px; font-weight: 800; color: #db2777; margin-top: 10px; }
        .btn-pay { background: #db2777; color: white; border: none; padding: 14px; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; width: 100%; margin-top: 20px; }
        .btn-pay:hover { background: #be185d; }
        .btn-back { display: inline-block; margin-bottom: 20px; color: #db2777; text-decoration: none; font-weight: 600; font-size: 14px; }
    </style>
</head>
<body>
    <header class="top-navbar"><a href="index.php?route=home" class="top-logo">✚ Telemedicine++ Medicine & Billing</a></header>
    <main class="main-content">
        <div class="panel">
            <a href="index.php?route=patient_dashboard" class="btn-back">&larr; Back to Dashboard</a>
            <h2>💊 Medicine Store Order & Combined Billing</h2>
            <div style="margin-bottom: 20px; font-size: 14px; color: #64748b;">
                Review your active pharmacy orders and consultation fees before proceeding to checkout.
            </div>
            <?php foreach ($_SESSION['payment_items'] as $key => $item): ?>
                <div class="bill-row">
                    <span><?= htmlspecialchars($item['name']) ?></span>
                    <strong>৳<?= number_format($item['price'], 2) ?></strong>
                </div>
            <?php endforeach; ?>
            <div class="bill-total">
                <span>Total Payable Amount</span>
                <span>৳<?= number_format($total_combined_amount, 2) ?></span>
            </div>
            <button class="btn-pay" onclick="alert('Payment gateway integration placeholder. Total: ৳<?= number_format($total_combined_amount, 2) ?>')">Proceed to Secure Payment &rarr;</button>
        </div>
    </main>
</body>
</html>
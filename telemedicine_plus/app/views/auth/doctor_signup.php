<?php
require_once 'db_doctor.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $specialty = trim($_POST['specialty']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $db_doctor->prepare("INSERT INTO doctors (name, email, specialty, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $specialty, $password]);
        $success = "Doctor account created successfully! You can now log in.";
    } catch (PDOException $e) {
        $error = "Email address already registered or database error.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Sign Up | Telemedicine++</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); width: 100%; max-width: 420px; }
        h2 { color: #0f766e; margin-bottom: 20px; text-align: center; font-size: 22px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; }
        input:focus { border-color: #0f766e; }
        .btn-submit { background: #0f766e; color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background 0.2s; margin-top: 10px; }
        .btn-submit:hover { background: #115e59; }
        .msg { padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: center; font-weight: 600; }
        .error { background: #fee2e2; color: #dc2626; }
        .success { background: #dcfce7; color: #15803d; }
        .link { text-align: center; margin-top: 15px; font-size: 13px; color: #64748b; }
        .link a { color: #0f766e; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="card">
    <h2>Doctor Registration</h2>
    <?php if (!empty($error)): ?><div class="msg error"><?= $error ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="msg success"><?= $success ?></div><?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="Dr. John Doe" required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="doctor@hospital.com" required>
        </div>
        <div class="form-group">
            <label>Medical Specialty</label>
            <input type="text" name="specialty" placeholder="e.g. Cardiologist" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-submit">Register as Doctor</button>
    </form>
    <div class="link">Already have an account? <a href="doctor_login.php">Log In</a></div>
</div>

</body>
</html>
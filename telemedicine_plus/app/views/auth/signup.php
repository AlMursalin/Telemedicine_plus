<?php
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    
    $specialty = 'Patient';
    if ($role === 'doctor') {
        $specialty = trim($_POST['specialty']);
    } elseif ($role === 'sitter') {
        $specialty = trim($_POST['sitter_qualification']) ?: 'Certified Hospital Sitter';
    }

    try {
        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            $error = "This email address is already registered. Please log in instead.";
        } else {
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, specialty) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password, $role, $specialty]);
            $success = ucfirst($role) . " account created successfully! You can now log in.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Telemedicine++</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .card { background: white; padding: 35px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); width: 100%; max-width: 420px; }
        h2 { color: #1e40af; margin-bottom: 20px; text-align: center; font-size: 22px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase; }
        input, select { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; background: #fff; }
        input:focus, select:focus { border-color: #2563eb; }
        .btn-submit { background: #2563eb; color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background 0.2s; margin-top: 10px; }
        .btn-submit:hover { background: #1d4ed8; }
        .msg { padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: center; font-weight: 600; }
        .error { background: #fee2e2; color: #dc2626; }
        .success { background: #dcfce7; color: #15803d; }
        .link { text-align: center; margin-top: 15px; font-size: 13px; color: #64748b; }
        .link a { color: #2563eb; text-decoration: none; font-weight: 600; }
    </style>
    <script>
        function toggleDynamicFields(value) {
            document.getElementById('specialty-group').style.display = (value === 'doctor') ? 'block' : 'none';
            document.getElementById('sitter-group').style.display = (value === 'sitter') ? 'block' : 'none';
        }
    </script>
</head>
<body>

<div class="card">
    <h2>Create Account</h2>
    <?php if (!empty($error)): ?><div class="msg error"><?= $error ?></div><?php endif; ?>
    <?php if (!empty($success)): ?><div class="msg success"><?= $success ?></div><?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Register As</label>
            <select name="role" onchange="toggleDynamicFields(this.value)" required>
                <option value="patient">Patient</option>
                <option value="doctor">Doctor</option>
                <option value="sitter">Hospital Sitter / Companion</option>
            </select>
        </div>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="Enter full name" required>
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter email address" required>
        </div>
        <div class="form-group" id="specialty-group" style="display: none;">
            <label>Medical Specialty</label>
            <input type="text" name="specialty" placeholder="e.g. Cardiologist">
        </div>
        <div class="form-group" id="sitter-group" style="display: none;">
            <label>Sitter Qualification / Certification</label>
            <input type="text" name="sitter_qualification" placeholder="e.g. Certified Bedside Companion">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-submit">Sign Up</button>
    </form>
    <div class="link">Already have an account? <a href="login.php">Log In</a></div>
</div>

</body>
</html>
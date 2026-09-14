<?php
require_once 'db_doctor.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $db_doctor->prepare("SELECT * FROM doctors WHERE email = ?");
    $stmt->execute([$email]);
    $doctor = $stmt->fetch();

    if ($doctor && password_verify($password, $doctor['password'])) {
        unset($_SESSION['user']);

        $_SESSION['doctor'] = [
            'id' => $doctor['id'],
            'name' => $doctor['name'],
            'email' => $doctor['email'],
            'specialty' => $doctor['specialty']
        ];
        
        header("Location: doctor_dashboard.php");
        exit;
    } else {
        $error = "Invalid doctor email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Login | Telemedicine++</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); width: 100%; max-width: 400px; }
        h2 { color: #0f766e; margin-bottom: 20px; text-align: center; font-size: 22px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; }
        input:focus { border-color: #0f766e; }
        .btn-login { background: #0f766e; color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background 0.2s; margin-top: 10px; }
        .btn-login:hover { background: #115e59; }
        .error-msg { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: center; font-weight: 600; }
        .link { text-align: center; margin-top: 15px; font-size: 13px; color: #64748b; }
        .link a { color: #0f766e; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="card">
    <h2>Doctor Portal Login</h2>
    <?php if (!empty($error)): ?><div class="error-msg"><?= $error ?></div><?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="doctor@hospital.com" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-login">Log In as Doctor</button>
    </form>
    <div class="link">Don't have an account? <a href="doctor_signup.php">Register Here</a></div>
</div>

</body>
</html>
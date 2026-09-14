<?php
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            
            if ($user['role'] === 'doctor') {
                header("Location: doctor_dashboard.php");
                exit;
            } elseif ($user['role'] === 'sitter') {
                header("Location: sitter_dashboard.php");
                exit;
            } else {
                header("Location: dashboard.php");
                exit;
            }
        } else {
            $error = "Invalid email or password.";
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
    <title>Log In | Telemedicine++</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .card { background: white; padding: 35px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); width: 100%; max-width: 400px; }
        h2 { color: #1e40af; margin-bottom: 20px; text-align: center; font-size: 22px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; background: #fff; }
        input:focus { border-color: #2563eb; }
        .btn-submit { background: #2563eb; color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; transition: background 0.2s; margin-top: 10px; }
        .btn-submit:hover { background: #1d4ed8; }
        .msg { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: center; font-weight: 600; }
        .link { text-align: center; margin-top: 15px; font-size: 13px; color: #64748b; }
        .link a { color: #2563eb; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="card">
    <h2>Log In</h2>
    <?php if (!empty($error)): ?><div class="msg"><?= $error ?></div><?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="Enter email address" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-submit">Log In</button>
    </form>
    <div class="link">Don't have an account? <a href="signup.php">Sign Up</a></div>
</div>

</body>
</html>
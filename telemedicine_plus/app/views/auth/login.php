<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Login | Telemedicine++</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
.card { background: white; padding: 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); width: 100%; max-width: 400px; }
h2 { color: #0f766e; margin-bottom: 20px; text-align: center; font-size: 22px; }
.form-group { margin-bottom: 15px; }
label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 6px; text-transform: uppercase; }
input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; }
.btn-login { background: #0f766e; color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-size: 15px; font-weight: 700; cursor: pointer; margin-top: 10px; }
.error-msg { background: #fee2e2; color: #dc2626; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: center; font-weight: 600; }
</style>
</head>
<body>
<div class="card">
    <h2>Portal Login</h2>
    <?php if (!empty($error)): ?><div class="error-msg"><?= $error ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group"><label>Email Address</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
        <button type="submit" class="btn-login">Log In</button>
    </form>
</div>
</body>
</html>
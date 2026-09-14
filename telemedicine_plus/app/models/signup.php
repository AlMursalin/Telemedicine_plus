<?php
if (!isset($db) && file_exists(__DIR__ . '/../../../config/db.php')) {
    require_once __DIR__ . '/../../../config/db.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Telemedicine++</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .auth-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 100%; max-width: 450px; }
        .auth-card h2 { margin-top: 0; color: #1e40af; text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        .btn-submit { width: 100%; background: #2563eb; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px; font-size: 15px; }
        .btn-submit:hover { background: #1d4ed8; }
        .error { color: #dc2626; font-size: 14px; text-align: center; margin-bottom: 15px; background: #fee2e2; padding: 10px; border-radius: 6px; }
        .success { color: #16a34a; font-size: 14px; text-align: center; margin-bottom: 15px; background: #dcfce3; padding: 10px; border-radius: 6px; }
        .login-link { text-align: center; margin-top: 20px; font-size: 14px; }
        .login-link a { color: #2563eb; text-decoration: none; font-weight: 600; }
        #extra-fields { display: none; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Create an Account</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php?route=signup">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required placeholder="John Doe">
            </div>
            
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="john@example.com">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            
            <div class="form-group">
                <label>Account Type</label>
                <select name="role" id="role-select" onchange="toggleExtraFields()" required>
                    <option value="patient">Patient</option>
                    <option value="doctor">Doctor</option>
                    <option value="sitter">Hospital Sitter</option>
                </select>
            </div>
            
            <div id="extra-fields">
                <div class="form-group" id="specialty-group" style="display: none;">
                    <label>Medical Specialty</label>
                    <input type="text" name="specialty" placeholder="e.g. Cardiologist">
                </div>
                <div class="form-group" id="sitter-group" style="display: none;">
                    <label>Sitter Qualification</label>
                    <input type="text" name="sitter_qualification" placeholder="e.g. Certified Nurse Assistant">
                </div>
            </div>

            <button type="submit" class="btn-submit">Sign Up</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="index.php?route=login">Log in here</a>
        </div>
    </div>

    <script>
        function toggleExtraFields() {
            const role = document.getElementById('role-select').value;
            const extraFields = document.getElementById('extra-fields');
            const specialtyGroup = document.getElementById('specialty-group');
            const sitterGroup = document.getElementById('sitter-group');

            if (role === 'doctor') {
                extraFields.style.display = 'block';
                specialtyGroup.style.display = 'block';
                sitterGroup.style.display = 'none';
            } else if (role === 'sitter') {
                extraFields.style.display = 'block';
                specialtyGroup.style.display = 'none';
                sitterGroup.style.display = 'block';
            } else {
                extraFields.style.display = 'none';
                specialtyGroup.style.display = 'none';
                sitterGroup.style.display = 'none';
            }
        }
    </script>
</body>
</html>
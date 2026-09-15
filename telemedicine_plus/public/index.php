<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();



}

if (!defined('REMEMBER_SECRET_KEY')) {
    define('REMEMBER_SECRET_KEY', 'tmp_9f4a7c2e1b6d8035a1f2c4e6b8d0a9f7');
}

function is_ajax_request() {
    $xrw = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return $xrw === 'xmlhttprequest' || strpos($accept, 'application/json') !== false;
}

function send_json_response($payload, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function issue_remember_cookie($user) {
    $expiry = time() + (30 * 24 * 60 * 60);
    $payload = $user['id'] . '|' . $user['role'] . '|' . $expiry;
    $signature = hash_hmac('sha256', $payload, REMEMBER_SECRET_KEY);
    $cookie_value = base64_encode($payload) . '.' . $signature;
    setcookie('remember_me', $cookie_value, [
        'expires' => $expiry,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS'])
    ]);
}

function clear_remember_cookie() {
    setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS'])
    ]);
}

if (!isset($_SESSION['user']) && isset($_COOKIE['remember_me'])) {
    $cookie_raw = $_COOKIE['remember_me'];
    $parts = explode('.', $cookie_raw);
    if (count($parts) === 2) {
        $encoded_payload = $parts[0];
        $signature = $parts[1];
        $payload = base64_decode($encoded_payload);
        $expected_signature = hash_hmac('sha256', $payload, REMEMBER_SECRET_KEY);
        if (hash_equals($expected_signature, $signature)) {
            $payload_parts = explode('|', $payload);
            if (count($payload_parts) === 3) {
                $cookie_user_id = $payload_parts[0];
                $cookie_role = $payload_parts[1];
                $cookie_expiry = (int) $payload_parts[2];
                if ($cookie_expiry > time()) {
                    $auth_stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = ?");
                    $auth_stmt->execute([$cookie_user_id, $cookie_role]);
                    $auth_user = $auth_stmt->fetch();
                    if ($auth_user) {
                        session_regenerate_id(true);
                        $_SESSION['user'] = $auth_user;
                    } else {
                        clear_remember_cookie();
                    }
                } else {
                    clear_remember_cookie();
                }
            } else {
                clear_remember_cookie();
            }
        } else {
            clear_remember_cookie();
        }
    } else {
        clear_remember_cookie();
    }
}

$route = $_GET['route'] ?? 'home';
$isLoggedIn = isset($_SESSION['user']);
$dashboard_route = 'login';

if ($isLoggedIn) {
    $role = $_SESSION['user']['role'] ?? 'patient';
    if ($role === 'doctor') {
        $dashboard_route = 'doctor_dashboard';
    } elseif ($role === 'sitter') {
        $dashboard_route = 'sitter_dashboard';
    } else {
        $dashboard_route = 'patient_dashboard';
    }
}

switch ($route) {
    case 'home':
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Telemedicine++ | Telemedicine Solution</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
                body { background-color: #ffffff; color: #333; overflow-x: hidden; }
                .hero-section { background: linear-gradient(105deg, #585de7 63%, #4b50d3 63%); color: white; min-height: 100vh; padding: 0 5%; position: relative; display: flex; flex-direction: column; overflow: hidden; }
                .navbar { display: flex; justify-content: space-between; align-items: center; padding: 30px 0; z-index: 10; }
                .logo { font-size: 24px; font-weight: bold; display: flex; align-items: center; gap: 8px; cursor: pointer; color: white; text-decoration: none; }
                .logo-icon { font-style: italic; font-size: 28px; color: #4ed3d5; }
                .nav-links { display: flex; gap: 30px; list-style: none; align-items: center; }
                .nav-links li a { color: #e0e0e0; text-decoration: none; font-size: 14px; font-weight: 500; transition: color 0.3s; }
                .nav-links li a:hover, .nav-links li a.active { color: #ffffff; font-weight: bold; }
                .btn-cyan { background-color: #2dd3e3; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 14px; border: none; cursor: pointer; transition: background 0.3s; box-shadow: 0 4px 10px rgba(45, 211, 227, 0.3); display: inline-block; }
                .btn-cyan:hover { background-color: #25b8c6; }
                .btn-outline { background-color: transparent; color: white; padding: 9px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 14px; border: 2px solid #2dd3e3; cursor: pointer; transition: all 0.3s; display: inline-block; }
                .btn-outline:hover { background-color: #2dd3e3; color: white; }
                .auth-buttons { display: flex; gap: 12px; align-items: center; }
                .hero-content { display: flex; flex: 1; align-items: center; justify-content: space-between; position: relative; z-index: 10; }
                .hero-text { max-width: 520px; margin-top: -50px; }
                .hero-text h1 { font-size: 48px; line-height: 1.2; margin-bottom: 20px; font-weight: 700; }
                .hero-text p { font-size: 14px; line-height: 1.6; color: #dcddef; margin-bottom: 35px; }
                .hero-image-container { position: absolute; right: 2%; bottom: 0; width: 48%; max-width: 580px; display: flex; justify-content: center; align-items: flex-end; }
                .hero-image-container img { width: 100%; display: block; border-top-left-radius: 12px; filter: drop-shadow(0px 10px 25px rgba(0,0,0,0.2)); }
                .features-section { padding: 100px 8%; display: flex; align-items: center; justify-content: space-between; gap: 50px; background-color: #ffffff; }
                .feature-image { flex: 1; max-width: 45%; }
                .feature-image img { width: 100%; border-radius: 12px; box-shadow: 0 15px 30px rgba(0,0,0,0.1); display: block; }
                .feature-grid { flex: 1; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
                .feature-card-link { text-decoration: none; color: inherit; display: block; }
                .feature-box { display: flex; flex-direction: column; align-items: flex-start; transition: transform 0.2s ease; }
                .feature-card-link:hover .feature-box { transform: translateY(-5px); }
                .icon-circle { width: 50px; height: 50px; background-color: #585de7; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin-bottom: 15px; }
                .icon-circle svg { width: 24px; height: 24px; fill: white; }
                .feature-box h3 { font-size: 18px; color: #333; margin-bottom: 10px; font-weight: 700; }
                .feature-box p { font-size: 13px; color: #888; line-height: 1.6; }
                .footer { text-align: center; padding: 25px 0; background-color: #f8fafc; border-top: 1px solid #e2e8f0; font-size: 13px; color: #64748b; }
                .footer span { color: #0f172a; font-weight: 600; }
            </style>
        </head>
        <body>
            <section class="hero-section">
                <nav class="navbar">
                    <a href="index.php?route=home" class="logo"><span class="logo-icon">§</span> Telemedicine++</a>
                    <ul class="nav-links">
                        <li><a href="index.php?route=home" class="active">Home</a></li>
                        <li><a href="index.php?route=doctors">Find Doctor</a></li>
                        <li><a href="index.php?route=medicines">Medicine Store</a></li>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Testimonial</a></li>
                    </ul>
                    <div class="auth-buttons">
                        <?php if ($isLoggedIn): ?>
                            <a href="index.php?route=<?= $dashboard_route ?>" class="btn-cyan">Dashboard</a>
                        <?php else: ?>
                            <a href="index.php?route=login" class="btn-outline">Log In</a>
                            <a href="index.php?route=signup" class="btn-cyan">Sign Up</a>
                        <?php endif; ?>
                    </div>
                </nav>
                <div class="hero-content">
                    <div class="hero-text">
                        <h1>Healthcare Anywhere.<br>Care Everywhere.</h1>
                        <p>Your health and well-being are our greatest priority. Whether you need expert medical consultations, a dedicated caring companion, or vital medicines, we empower your healing journey with accessible, compassionate care every step of the way.</p>
                        <?php if ($isLoggedIn): ?>
                            <a href="index.php?route=<?= $dashboard_route ?>" class="btn-cyan">Go to Dashboard</a>
                        <?php else: ?>
                            <div style="display: flex; gap: 15px;">
                                <a href="index.php?route=signup" class="btn-cyan">Get Started</a>
                                <a href="index.php?route=login" class="btn-outline">Log In</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="hero-image-container">
                        <img src="tele_logo02.png" alt="Telemedicine Telehealth Banner"> 
                    </div>
                </div>
            </section>
            <section class="features-section">
                <div class="feature-image">
                    <img src="tele_pic02.png" alt="Online Consultation Feature">
                </div>
                <div class="feature-grid">
                    <a href="index.php?route=doctors" class="feature-card-link">
                        <div class="feature-box">
                            <div class="icon-circle"><svg viewBox="0 0 24 24"><path d="M20 6h-4V4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zM10 4h4v2h-4V4zm6 11h-3v3h-2v-3H8v-2h3v-3h2v3h3v2z"/></svg></div>
                            <h3>Find Doctor</h3>
                            <p>Browse verified specialists across Bangladesh, check their consultation fees in Taka, and book instantly.</p>
                        </div>
                    </a>
                    <a href="index.php?route=meeting" class="feature-card-link">
                        <div class="feature-box">
                            <div class="icon-circle"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm4.5-12h-9c-.28 0-.5.22-.5.5v5c0 2.76 2.24 5 5 5s5-2.24 5-5v-5c0-.28-.22-.5-.5-.5z"/></svg></div>
                            <h3>Consultation</h3>
                            <p>Connect with expert medical professionals via seamless online video consultations right from your home.</p>
                        </div>
                    </a>
                    <a href="index.php?route=patient_dashboard" class="feature-card-link">
                        <div class="feature-box">
                            <div class="icon-circle"><svg viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg></div>
                            <h3>Book Appointment</h3>
                            <p>Select your doctor, schedule slots, pay securely online in Taka, and join live online video sessions.</p>
                        </div>
                    </a>
                    <a href="index.php?route=medicines" class="feature-card-link">
                        <div class="feature-box">
                            <div class="icon-circle"><svg viewBox="0 0 24 24"><path d="M21 2H3c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h7v2H8v2h8v-2h-2v-2h7c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H3V4h18v12z"/></svg></div>
                            <h3>Medicine Store</h3>
                            <p>Order standard Bangladeshi medications online, add items to your cart, and pay securely online.</p>
                        </div>
                    </a>
                </div>
            </section>
            <footer class="footer">
                <strong>&copy; 2026 <span>Md Al Mursalin</span>. All rights reserved. &bull;</strong> <span>Telemedicine++</span>
            </footer>
        </body>
        </html>
        <?php
        break;

    case 'login':
        $error = '';
        $field_errors = [];
        $ajax = is_ajax_request();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $remember = isset($_POST['remember']) && $_POST['remember'] == '1';

            if ($email === '') {
                $field_errors['email'] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $field_errors['email'] = 'Enter a valid email address.';
            }

            if ($password === '') {
                $field_errors['password'] = 'Password is required.';
            } elseif (strlen($password) < 6) {
                $field_errors['password'] = 'Password must be at least 6 characters.';
            }

            if (empty($field_errors)) {
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = $user;
                    $target_route = $user['role'] === 'doctor' ? 'doctor_dashboard' : ($user['role'] === 'sitter' ? 'sitter_dashboard' : 'patient_dashboard');

                    if ($remember) {
                        issue_remember_cookie($user);
                    } else {
                        clear_remember_cookie();
                    }

                    if ($ajax) {
                        send_json_response(['success' => true, 'redirect' => 'index.php?route=' . $target_route]);
                    }
                    header("Location: index.php?route=" . $target_route);
                    exit;
                } else {
                    $field_errors['general'] = 'Invalid email or password.';
                }
            }

            if ($ajax) {
                send_json_response(['success' => false, 'errors' => $field_errors], 422);
            }
            $error = $field_errors['general'] ?? implode(' ', $field_errors);
        }
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login | Telemedicine++</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
                .auth-card { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 100%; max-width: 400px; }
                .back-home { text-align: center; margin-bottom: 15px; }
                .back-home a { color: #64748b; text-decoration: none; font-size: 14px; font-weight: 600; transition: color 0.2s; }
                .back-home a:hover { color: #2563eb; }
                .auth-card h2 { margin-top: 0; color: #1e40af; text-align: center; margin-bottom: 20px; }
                .form-group { margin-bottom: 15px; }
                .form-group label { display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px; color: #333; }
                .form-group input { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
                .btn-submit { width: 100%; background: #2563eb; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px; font-size: 15px; }
                .btn-submit:hover { background: #1d4ed8; }
                .error { color: #dc2626; font-size: 14px; text-align: center; margin-bottom: 15px; background: #fee2e2; padding: 10px; border-radius: 6px; }
                .signup-link { text-align: center; margin-top: 20px; font-size: 14px; }
                .signup-link a { color: #2563eb; text-decoration: none; font-weight: 600; }
            </style>
        </head>
        <body>
            <div class="auth-card">
                <div class="back-home"><a href="index.php?route=home">&larr; Back to Home</a></div>
                <h2>Welcome Back</h2>
                <div id="form-message" class="error" style="display: none;"></div>
                <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                <form method="POST" action="index.php?route=login" id="login-form" novalidate>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" id="login-email" required placeholder="john@example.com">
                        <span class="field-error" id="login-email-error" style="color:#dc2626;font-size:12px;"></span>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" id="login-password" required placeholder="••••••••">
                        <span class="field-error" id="login-password-error" style="color:#dc2626;font-size:12px;"></span>
                    </div>
                    <div class="form-group" style="display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="remember" id="login-remember" value="1" style="width:auto;">
                        <label for="login-remember" style="margin:0;font-weight:500;">Remember me</label>
                    </div>
                    <button type="submit" class="btn-submit" id="login-submit-btn">Log In</button>
                </form>
                <div class="signup-link">
                    Don't have an account? <a href="index.php?route=signup">Sign up here</a>
                </div>
            </div>
            <script>
            document.getElementById('login-form').addEventListener('submit', function (event) {
                event.preventDefault();
                var emailField = document.getElementById('login-email');
                var passwordField = document.getElementById('login-password');
                var emailError = document.getElementById('login-email-error');
                var passwordError = document.getElementById('login-password-error');
                var formMessage = document.getElementById('form-message');
                var submitBtn = document.getElementById('login-submit-btn');
                emailError.textContent = '';
                passwordError.textContent = '';
                formMessage.style.display = 'none';
                formMessage.textContent = '';
                var emailValue = emailField.value.trim();
                var passwordValue = passwordField.value.trim();
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                var isValid = true;
                if (emailValue === '') {
                    emailError.textContent = 'Email is required.';
                    isValid = false;
                } else if (!emailPattern.test(emailValue)) {
                    emailError.textContent = 'Enter a valid email address.';
                    isValid = false;
                }
                if (passwordValue === '') {
                    passwordError.textContent = 'Password is required.';
                    isValid = false;
                } else if (passwordValue.length < 6) {
                    passwordError.textContent = 'Password must be at least 6 characters.';
                    isValid = false;
                }
                if (!isValid) {
                    return;
                }
                submitBtn.disabled = true;
                submitBtn.textContent = 'Logging in...';
                var formData = new URLSearchParams();
                formData.append('email', emailValue);
                formData.append('password', passwordValue);
                formData.append('remember', document.getElementById('login-remember').checked ? '1' : '0');
                fetch('index.php?route=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData.toString(),
                    credentials: 'same-origin'
                })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { status: response.status, body: data };
                    });
                })
                .then(function (result) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Log In';
                    if (result.body.success) {
                        window.location.href = result.body.redirect;
                        return;
                    }
                    var errors = result.body.errors || {};
                    if (errors.email) { emailError.textContent = errors.email; }
                    if (errors.password) { passwordError.textContent = errors.password; }
                    if (errors.general) {
                        formMessage.textContent = errors.general;
                        formMessage.style.display = 'block';
                    }
                })
                .catch(function () {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Log In';
                    formMessage.textContent = 'Something went wrong. Please try again.';
                    formMessage.style.display = 'block';
                });
            });
            </script>
        </body>
        </html>
        <?php
        break;

    case 'signup':
        $error = ''; $success = '';
        $field_errors = [];
        $ajax = is_ajax_request();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $raw_password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'patient';
            $allowed_roles = ['patient', 'doctor', 'sitter'];
            $specialty_input = trim($_POST['specialty'] ?? '');
            $sitter_input = trim($_POST['sitter_qualification'] ?? '');

            if ($name === '') {
                $field_errors['name'] = 'Full name is required.';
            } elseif (strlen($name) < 2) {
                $field_errors['name'] = 'Full name must be at least 2 characters.';
            }

            if ($email === '') {
                $field_errors['email'] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $field_errors['email'] = 'Enter a valid email address.';
            }

            if ($raw_password === '') {
                $field_errors['password'] = 'Password is required.';
            } elseif (strlen($raw_password) < 6) {
                $field_errors['password'] = 'Password must be at least 6 characters.';
            }

            if (!in_array($role, $allowed_roles, true)) {
                $field_errors['role'] = 'Select a valid account type.';
            }

            if ($role === 'doctor' && $specialty_input === '') {
                $field_errors['specialty'] = 'Medical specialty is required for doctors.';
            }

            if ($role === 'sitter' && $sitter_input === '') {
                $field_errors['sitter_qualification'] = 'Sitter qualification is required.';
            }

            if (empty($field_errors)) {
                $specialty = $role === 'doctor' ? $specialty_input : ($role === 'sitter' ? $sitter_input : 'Patient');
                try {
                    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $field_errors['email'] = 'Email already registered.';
                    } else {
                        $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);
                        $ins = $db->prepare("INSERT INTO users (name, email, password, role, specialty) VALUES (?, ?, ?, ?, ?)");
                        $ins->execute([$name, $email, $hashed_password, $role, $specialty]);
                        $success = "Account created successfully! You can now log in.";
                        if ($ajax) {
                            send_json_response(['success' => true, 'message' => $success, 'redirect' => 'index.php?route=login']);
                        }
                    }
                } catch (Exception $e) {
                    $field_errors['general'] = 'Registration failed.';
                }
            }

            if (empty($success) && $ajax) {
                send_json_response(['success' => false, 'errors' => $field_errors], 422);
            }
            $error = $field_errors['general'] ?? implode(' ', $field_errors);
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
                .back-home { text-align: center; margin-bottom: 15px; }
                .back-home a { color: #64748b; text-decoration: none; font-size: 14px; font-weight: 600; transition: color 0.2s; }
                .back-home a:hover { color: #2563eb; }
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
                <div class="back-home"><a href="index.php?route=home">&larr; Back to Home</a></div>
                <h2>Create an Account</h2>
                
                <div id="form-message" class="error" style="display: none;"></div>

                <?php if (!empty($error)): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="POST" action="index.php?route=signup" id="signup-form" novalidate>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" id="signup-name" required placeholder="John Doe">
                        <span class="field-error" id="signup-name-error" style="color:#dc2626;font-size:12px;"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" id="signup-email" required placeholder="john@example.com">
                        <span class="field-error" id="signup-email-error" style="color:#dc2626;font-size:12px;"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" id="signup-password" required placeholder="••••••••">
                        <span class="field-error" id="signup-password-error" style="color:#dc2626;font-size:12px;"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Account Type</label>
                        <select name="role" id="role-select" required>
                            <option value="patient">Patient</option>
                            <option value="doctor">Doctor</option>
                            <option value="sitter">Hospital Sitter</option>
                        </select>
                        <span class="field-error" id="signup-role-error" style="color:#dc2626;font-size:12px;"></span>
                    </div>
                    
                    <div id="extra-fields">
                        <div class="form-group" id="specialty-group" style="display: none;">
                            <label>Medical Specialty</label>
                            <input type="text" name="specialty" id="signup-specialty" placeholder="e.g. Cardiologist">
                            <span class="field-error" id="signup-specialty-error" style="color:#dc2626;font-size:12px;"></span>
                        </div>
                        <div class="form-group" id="sitter-group" style="display: none;">
                            <label>Sitter Qualification</label>
                            <input type="text" name="sitter_qualification" id="signup-sitter" placeholder="e.g. Certified Nurse Assistant">
                            <span class="field-error" id="signup-sitter-error" style="color:#dc2626;font-size:12px;"></span>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="signup-submit-btn">Sign Up</button>
                </form>
                
                <div class="login-link">
                    Already have an account? <a href="index.php?route=login">Log in here</a>
                </div>
            </div>

            <script>
            function toggleExtraFields() {
                var role = document.getElementById('role-select').value;
                var extraFields = document.getElementById('extra-fields');
                var specialtyGroup = document.getElementById('specialty-group');
                var sitterGroup = document.getElementById('sitter-group');
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
            document.getElementById('role-select').addEventListener('change', toggleExtraFields);
            document.getElementById('signup-form').addEventListener('submit', function (event) {
                event.preventDefault();
                var fields = {
                    name: document.getElementById('signup-name'),
                    email: document.getElementById('signup-email'),
                    password: document.getElementById('signup-password'),
                    role: document.getElementById('role-select'),
                    specialty: document.getElementById('signup-specialty'),
                    sitter_qualification: document.getElementById('signup-sitter')
                };
                var errorEls = {
                    name: document.getElementById('signup-name-error'),
                    email: document.getElementById('signup-email-error'),
                    password: document.getElementById('signup-password-error'),
                    role: document.getElementById('signup-role-error'),
                    specialty: document.getElementById('signup-specialty-error'),
                    sitter_qualification: document.getElementById('signup-sitter-error')
                };
                Object.keys(errorEls).forEach(function (key) { errorEls[key].textContent = ''; });
                var formMessage = document.getElementById('form-message');
                formMessage.style.display = 'none';
                formMessage.textContent = '';
                var submitBtn = document.getElementById('signup-submit-btn');
                var nameValue = fields.name.value.trim();
                var emailValue = fields.email.value.trim();
                var passwordValue = fields.password.value.trim();
                var roleValue = fields.role.value;
                var specialtyValue = fields.specialty.value.trim();
                var sitterValue = fields.sitter_qualification.value.trim();
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                var isValid = true;
                if (nameValue === '') {
                    errorEls.name.textContent = 'Full name is required.';
                    isValid = false;
                } else if (nameValue.length < 2) {
                    errorEls.name.textContent = 'Full name must be at least 2 characters.';
                    isValid = false;
                }
                if (emailValue === '') {
                    errorEls.email.textContent = 'Email is required.';
                    isValid = false;
                } else if (!emailPattern.test(emailValue)) {
                    errorEls.email.textContent = 'Enter a valid email address.';
                    isValid = false;
                }
                if (passwordValue === '') {
                    errorEls.password.textContent = 'Password is required.';
                    isValid = false;
                } else if (passwordValue.length < 6) {
                    errorEls.password.textContent = 'Password must be at least 6 characters.';
                    isValid = false;
                }
                if (roleValue === 'doctor' && specialtyValue === '') {
                    errorEls.specialty.textContent = 'Medical specialty is required for doctors.';
                    isValid = false;
                }
                if (roleValue === 'sitter' && sitterValue === '') {
                    errorEls.sitter_qualification.textContent = 'Sitter qualification is required.';
                    isValid = false;
                }
                if (!isValid) {
                    return;
                }
                submitBtn.disabled = true;
                submitBtn.textContent = 'Creating account...';
                var formData = new URLSearchParams();
                formData.append('name', nameValue);
                formData.append('email', emailValue);
                formData.append('password', passwordValue);
                formData.append('role', roleValue);
                formData.append('specialty', specialtyValue);
                formData.append('sitter_qualification', sitterValue);
                fetch('index.php?route=signup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData.toString(),
                    credentials: 'same-origin'
                })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { status: response.status, body: data };
                    });
                })
                .then(function (result) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign Up';
                    if (result.body.success) {
                        formMessage.className = 'success';
                        formMessage.textContent = result.body.message;
                        formMessage.style.display = 'block';
                        document.getElementById('signup-form').reset();
                        toggleExtraFields();
                        setTimeout(function () {
                            window.location.href = result.body.redirect;
                        }, 1200);
                        return;
                    }
                    var errors = result.body.errors || {};
                    Object.keys(errors).forEach(function (key) {
                        if (errorEls[key]) {
                            errorEls[key].textContent = errors[key];
                        } else {
                            formMessage.className = 'error';
                            formMessage.textContent = errors[key];
                            formMessage.style.display = 'block';
                        }
                    });
                })
                .catch(function () {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign Up';
                    formMessage.className = 'error';
                    formMessage.textContent = 'Something went wrong. Please try again.';
                    formMessage.style.display = 'block';
                });
            });
            </script>
        </body>
        </html>
        <?php
        break;

    case 'logout':
        unset($_SESSION['user']);
        session_destroy();
        clear_remember_cookie();
        header("Location: index.php?route=home");
        exit;
        break;

    case 'patient_dashboard':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') { header("Location: index.php?route=login"); exit; }
        $user = $_SESSION['user'];
        $doctors = [];
        try {
            if (isset($db)) {
                $stmt = $db->prepare("SELECT id, name, specialty, consultation_fee FROM users WHERE role = 'doctor' ORDER BY name ASC");
                $stmt->execute();
                $doctors = $stmt->fetchAll();
            }
        } catch (Exception $e) { $doctors = []; }

        $sitters = [];
        try {
            if (isset($db)) {
                $stmt_s = $db->prepare("SELECT id, name, specialty, hourly_rate FROM users WHERE role = 'sitter' AND availability = 'Available' ORDER BY name ASC");
                $stmt_s->execute();
                $sitters = $stmt_s->fetchAll();
            }
        } catch (Exception $e) { $sitters = []; }
        
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Patient Dashboard | Telemedicine++</title>
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
                .sidebar-avatar { width: 65px; height: 65px; background: #dbeafe; color: #1d4ed8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 10px; font-weight: bold; }
                .sidebar-profile h3 { font-size: 15px; color: #1e293b; font-weight: 700; margin-bottom: 2px; }
                .sidebar-profile span { font-size: 12px; color: #3b82f6; font-weight: 600; text-transform: uppercase; }

                .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 5px; }
                .nav-item a { display: flex; align-items: center; gap: 12px; padding: 11px 15px; border-radius: 8px; color: #64748b; text-decoration: none; font-size: 13.5px; font-weight: 500; transition: all 0.2s; }
                .nav-item a:hover, .nav-item a.active { background: #eff6ff; color: #1d4ed8; font-weight: 600; }
                .sidebar-divider { height: 1px; background: #f1f5f9; margin: 15px 0; }

                .main-content { margin-top: 65px; margin-left: 260px; flex: 1; padding: 30px; background: #f8fafc; min-height: calc(100vh - 65px); }
                
                .dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px; }
                .welcome-box h1 { font-size: 26px; color: #0f172a; font-weight: 800; margin-bottom: 4px; }
                .welcome-box p { font-size: 14px; color: #64748b; }
                
                .datetime-widget { background: white; padding: 12px 20px; border-radius: 12px; display: flex; gap: 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
                .dt-item { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #475569; }
                .dt-item strong { color: #0f172a; display: block; font-size: 12px; }

                .action-cards-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 15px; margin-bottom: 25px; }
                .action-card { background: white; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px 15px; text-align: center; text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.01); display: flex; flex-direction: column; align-items: center; }
                .action-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.06); border-color: #cbd5e1; }
                .action-icon { width: 50px; height: 50px; background: #f0fdf4; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 12px; }
                .action-card h3 { font-size: 14.5px; color: #1e293b; font-weight: 700; margin-bottom: 6px; }
                .action-card p { font-size: 11.5px; color: #64748b; line-height: 1.4; margin-bottom: 15px; }
                .action-arrow { margin-top: auto; color: #94a3b8; font-size: 16px; transition: transform 0.2s; }
                .action-card:hover .action-arrow { transform: translateX(4px); color: #2563eb; }

                .directory-tabs { display: flex; gap: 12px; margin-bottom: 20px; }
                .tab-btn { padding: 11px 22px; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; border: 1px solid #cbd5e1; transition: all 0.2s; background: white; color: #475569; display: flex; align-items: center; gap: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
                .tab-btn.active-doc { background: #2563eb; color: white; border-color: #2563eb; }
                .tab-btn.active-sitter { background: #059669; color: white; border-color: #059669; }

                .doctors-section { margin-bottom: 25px; }
                .doctor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 20px; }
                
                .doctor-card { 
                    background: white; 
                    border: 1px solid #e2e8f0; 
                    border-radius: 16px; 
                    padding: 24px 20px; 
                    text-align: center; 
                    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01); 
                    transition: transform 0.2s, box-shadow 0.2s;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                }
                .doctor-card:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.06); border-color: #cbd5e1; }
                
                .doc-avatar-box { 
                    width: 60px; 
                    height: 60px; 
                    background: #e0f2fe; 
                    color: #0284c7; 
                    border-radius: 50%; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    font-size: 24px; 
                    font-weight: 800; 
                    margin: 0 auto 14px; 
                }
                
                .doc-name { font-size: 16.5px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
                .doc-specialty { font-size: 13.5px; color: #2563eb; font-weight: 600; margin-bottom: 8px; display: block; }
                
                .doc-id-badge { 
                    font-size: 11.5px; 
                    background: #f1f5f9; 
                    color: #475569; 
                    padding: 3px 10px; 
                    border-radius: 6px; 
                    display: inline-block; 
                    margin-bottom: 12px; 
                    font-weight: 600; 
                }
                
                .doc-fee { font-size: 14px; color: #475569; margin-bottom: 20px; }
                .doc-fee strong { color: #0f172a; font-weight: 700; }
                
                .btn-book-doc { 
                    background: #2563eb; 
                    color: white; 
                    width: 100%; 
                    padding: 11px; 
                    border-radius: 10px; 
                    text-decoration: none; 
                    font-size: 13.5px; 
                    font-weight: 700; 
                    transition: background 0.2s; 
                    margin-top: auto;
                    box-shadow: 0 2px 4px rgba(37,99,235,0.2);
                }
                .btn-book-doc:hover { background: #1d4ed8; }

                .btn-book-sitter { 
                    background: #059669; 
                    color: white; 
                    width: 100%; 
                    padding: 11px; 
                    border-radius: 10px; 
                    text-decoration: none; 
                    font-size: 13.5px; 
                    font-weight: 700; 
                    transition: background 0.2s; 
                    margin-top: auto;
                    box-shadow: 0 2px 4px rgba(5,150,105,0.2);
                }
                .btn-book-sitter:hover { background: #047857; }
            </style>
            <script>
                function switchDirectoryTab(type) {
                    const docSection = document.getElementById('doctors-directory-box');
                    const sitterSection = document.getElementById('sitters-directory-box');
                    const btnDoc = document.getElementById('tab-doc-btn');
                    const btnSitter = document.getElementById('tab-sitter-btn');

                    if (type === 'doctors') {
                        docSection.style.display = 'grid';
                        sitterSection.style.display = 'none';
                        btnDoc.className = 'tab-btn active-doc';
                        btnSitter.className = 'tab-btn';
                    } else {
                        docSection.style.display = 'none';
                        sitterSection.style.display = 'grid';
                        btnSitter.className = 'tab-btn active-sitter';
                        btnDoc.className = 'tab-btn';
                    }
                }

                function updateLiveClock() {
                    const now = new Date();
                    const dateOptions = { month: 'short', day: 'numeric', year: 'numeric' };
                    document.getElementById('live-date').innerText = now.toLocaleDateString('en-US', dateOptions);
                    const timeOptions = { hour: '2-digit', minute: '2-digit', hour12: true };
                    document.getElementById('live-time').innerText = now.toLocaleTimeString('en-US', timeOptions);
                }
                setInterval(updateLiveClock, 1000);
                window.onload = updateLiveClock;
            </script>
        </head>
        <body>

            <header class="top-navbar">
                <a href="index.php?route=home" class="top-logo"><span>Telemedicine++</span></a>
                <ul class="top-nav-links">
                    <li><a href="index.php?route=home">🏠 Home</a></li>
                    <li><a href="index.php?route=patient_dashboard" class="active">📊 Dashboard</a></li>
                    <li><a href="index.php?route=logout" style="color: #fca5a5;">Log Out (<?= htmlspecialchars($user['name']) ?>)</a></li>
                </ul>
            </header>

            <aside class="sidebar">
                <div class="sidebar-profile">
                    <div class="sidebar-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                    <h3><?= htmlspecialchars($user['name']) ?></h3>
                    <span>Patient</span>
                </div>

                <?php $current_route = $_GET['route'] ?? 'patient_dashboard'; ?>
                <ul class="nav-menu">
                    <li class="nav-item"><a href="index.php?route=patient_dashboard" class="<?= $current_route == 'patient_dashboard' ? 'active' : '' ?>">📊 Dashboard</a></li>
                    <li class="nav-item"><a href="index.php?route=patient_appointments" class="<?= $current_route == 'patient_appointments' ? 'active' : '' ?>">📅 Track Schedule</a></li>
                    <li class="nav-item"><a href="index.php?route=profile" class="<?= $current_route == 'profile' ? 'active' : '' ?>">👤 Edit Profile</a></li>
                    <li class="nav-item"><a href="index.php?route=patient_appointments" class="<?= $current_route == 'patient_appointments' ? 'active' : '' ?>">📋 Appointments</a></li>
                    <li class="nav-item"><a href="index.php?route=meeting" class="<?= $current_route == 'meeting' ? 'active' : '' ?>">📹 Live Appointment</a></li>
                    <li class="nav-item"><a href="index.php?route=medicines" class="<?= $current_route == 'medicines' ? 'active' : '' ?>">💳 Make Payment</a></li>
                    <div class="sidebar-divider"></div>
                    <li class="nav-item"><a href="index.php?route=logout">🚪 Log Out</a></li>
                </ul>
            </aside>

            <main class="main-content">
                <div class="dashboard-header">
                    <div class="welcome-box">
                        <h1>Hello, <?= htmlspecialchars($user['name']) ?>! 👋</h1>
                        <p>Welcome back! Here's your health overview.</p>
                    </div>
                    <div class="datetime-widget">
                        <div class="dt-item">
                            <span style="font-size: 20px;">📅</span>
                            <div>
                                <strong>Today</strong>
                                <span id="live-date">Loading...</span>
                            </div>
                        </div>
                        <div style="width: 1px; background: #e2e8f0;"></div>
                        <div class="dt-item">
                            <span style="font-size: 20px;">🕒</span>
                            <div>
                                <strong>Time</strong>
                                <span id="live-time">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="action-cards-grid">
                    <a href="index.php?route=patient_appointments" class="action-card">
                        <div class="action-icon" style="background: #eff6ff; color: #2563eb;">📅</div>
                        <h3>Track Schedule</h3>
                        <p>View your upcoming dates and events.</p>
                        <div class="action-arrow">&rarr;</div>
                    </a>
                    <a href="index.php?route=profile" class="action-card">
                        <div class="action-icon" style="background: #f5f3ff; color: #7c3aed;">👤</div>
                        <h3>Edit Profile</h3>
                        <p>Update your personal information.</p>
                        <div class="action-arrow">&rarr;</div>
                    </a>
                    <a href="index.php?route=patient_appointments" class="action-card">
                        <div class="action-icon" style="background: #fff7ed; color: #ea580c;">📋</div>
                        <h3>Appointments</h3>
                        <p>View or update your appointments.</p>
                        <div class="action-arrow">&rarr;</div>
                    </a>
                    <a href="index.php?route=meeting" class="action-card">
                        <div class="action-icon" style="background: #f0fdf4; color: #16a34a;">📹</div>
                        <h3>Live Appointment</h3>
                        <p>Join a WebRTC call with your doctor.</p>
                        <div class="action-arrow">&rarr;</div>
                    </a>
                    <a href="index.php?route=medicines" class="action-card">
                        <div class="action-icon" style="background: #fdf2f8; color: #db2777;">💳</div>
                        <h3>Make Payment</h3>
                        <p>Pay your bills and view payment history.</p>
                        <div class="action-arrow">&rarr;</div>
                    </a>
                </div>

                <div class="doctors-section">
                    <div class="directory-tabs">
                        <button id="tab-doc-btn" class="tab-btn active-doc" onclick="switchDirectoryTab('doctors')">🩺 Browse Doctors & Specialists</button>
                        <button id="tab-sitter-btn" class="tab-btn" onclick="switchDirectoryTab('sitters')">🛏️ Browse Hospital Sitters</button>
                    </div>

                    <div id="doctors-directory-box" class="doctor-grid">
                        <?php if (empty($doctors)): ?>
                            <p style="color: #64748b; font-size: 14px; grid-column: 1/-1;">No doctors have registered on the portal yet.</p>
                        <?php else: ?>
                            <?php foreach ($doctors as $doc): ?>
                                <div class="doctor-card">
                                    <div class="doc-avatar-box"><?= strtoupper(substr($doc['name'], 0, 1)) ?></div>
                                    <div class="doc-name"><?= htmlspecialchars($doc['name']) ?></div>
                                    <span class="doc-specialty"><?= htmlspecialchars($doc['specialty']) ?></span>
                                    <div class="doc-id-badge">Doctor ID: #<?= $doc['id'] ?></div>
                                    <div class="doc-fee">Fee: <strong>৳<?= number_format($doc['consultation_fee'] ?? 1000, 2) ?></strong></div>
                                    <a href="index.php?route=book_appointment&doctor_id=<?= $doc['id'] ?>" class="btn-book-doc">Book Appointment &rarr;</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div id="sitters-directory-box" class="doctor-grid" style="display: none;">
                        <?php if (empty($sitters)): ?>
                            <p style="color: #64748b; font-size: 14px; grid-column: 1/-1;">No available hospital sitters at the moment.</p>
                        <?php else: ?>
                            <?php foreach ($sitters as $sitter): ?>
                                <div class="doctor-card" style="border-top: 4px solid #059669;">
                                    <div class="doc-avatar-box" style="background: #d1fae5; color: #059669;"><?= strtoupper(substr($sitter['name'], 0, 1)) ?></div>
                                    <div class="doc-name"><?= htmlspecialchars($sitter['name']) ?></div>
                                    <span class="doc-specialty" style="color: #059669;"><?= htmlspecialchars($sitter['specialty']) ?></span>
                                    <div class="doc-id-badge">Sitter ID: #<?= $sitter['id'] ?></div>
                                    <div class="doc-fee">Rate: <strong>৳<?= number_format($sitter['hourly_rate'] ?? 500, 2) ?> / hr</strong></div>
                                    <a href="index.php?route=book_sitter&sitter_id=<?= $sitter['id'] ?>" class="btn-book-sitter">Request Sitter &rarr;</a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </body>
        </html>
        <?php
        break;

    case 'doctors':
        $doc_stmt = $db->prepare("SELECT id, name, specialty, consultation_fee FROM users WHERE role = 'doctor' ORDER BY name ASC");
        $doc_stmt->execute();
        $doctors = $doc_stmt->fetchAll();
        require_once __DIR__ . '/../app/views/patient/doctors.php';
        break;

    case 'medicines':
        if (!isset($_SESSION['user'])) { header("Location: index.php?route=login"); exit; }
        $user = $_SESSION['user'];
        if (!isset($_SESSION['payment_items'])) {
            $_SESSION['payment_items'] = [
                'doctor' => ['name' => 'Doctor Consultation Fee', 'price' => 1500, 'active' => true],
                'medicine' => ['name' => 'Medicine Store Order', 'price' => 750, 'active' => true]
            ];
        }
        $total_combined_amount = array_sum(array_column(array_filter($_SESSION['payment_items'], fn($i)=>$i['active']), 'price'));
        require_once __DIR__ . '/../app/views/patient/medicines.php';
        break;

    case 'meeting':
        if (!isset($_SESSION['user'])) { header("Location: index.php?route=login"); exit; }
        $user = $_SESSION['user'];
        require_once __DIR__ . '/../app/views/patient/meeting.php';
        break;

    case 'book_appointment':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') { header("Location: index.php?route=login"); exit; }
        $patient = $_SESSION['user'];
        $doctor_id = $_GET['doctor_id'] ?? 0;
        $doc_s = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'doctor'");
        $doc_s->execute([$doctor_id]);
        $doctor = $doc_s->fetch();
        $success_msg = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $doctor) {
            $stmt = $db->prepare("INSERT INTO appointments (patient_name, doctor_name, specialty, appointment_date, appointment_time, fee, patient_condition, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Scheduled')");
            $stmt->execute([
                $patient['name'],
                $doctor['name'],
                $doctor['specialty'] ?? 'General Specialist',
                $_POST['appointment_date'],
                $_POST['appointment_time'],
                $doctor['consultation_fee'] ?? 1000,
                trim($_POST['patient_condition'])
            ]);
            $success_msg = "Appointment booked successfully with Dr. " . htmlspecialchars($doctor['name']) . "!";
        }
        require_once __DIR__ . '/../app/views/patient/book_appointment.php';
        break;

    case 'book_sitter':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') { header("Location: index.php?route=login"); exit; }
        $patient = $_SESSION['user'];
        $sitter_id = $_GET['sitter_id'] ?? 0;
        $s_stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND role = 'sitter'");
        $s_stmt->execute([$sitter_id]);
        $sitter = $s_stmt->fetch();
        $success_msg = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $sitter) {
            $hours = intval($_POST['hours']);
            $hourly_rate = floatval($sitter['hourly_rate'] ?? 500);
            $total_cost = $hourly_rate * $hours;
            $insert = $db->prepare("INSERT INTO sitter_bookings (patient_name, sitter_name, hourly_rate, hours, total_cost, booking_date, shift_timing, patient_condition, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Active')");
            $insert->execute([
                $patient['name'],
                $sitter['name'],
                $hourly_rate,
                $hours,
                $total_cost,
                $_POST['booking_date'],
                trim($_POST['shift_timing']),
                trim($_POST['patient_condition'])
            ]);
            $success_msg = "Bedside sitter booked successfully! Total Cost: ৳" . number_format($total_cost, 2);
        }
        require_once __DIR__ . '/../app/views/patient/book_sitter.php';
        break;

    case 'patient_appointments':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'patient') { header("Location: index.php?route=login"); exit; }
        $patient = $_SESSION['user'];
        $stmt = $db->prepare("SELECT * FROM appointments WHERE patient_name = ? ORDER BY appointment_date DESC");
        $stmt->execute([$patient['name']]);
        $appointments = $stmt->fetchAll();
        require_once __DIR__ . '/../app/views/patient/patient_appointments.php';
        break;

    case 'profile':
        if (!isset($_SESSION['user'])) { header("Location: index.php?route=login"); exit; }
        $user = $_SESSION['user'];
        $success_message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $address = trim($_POST['address']);
            $up = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $up->execute([$name, $email, $phone, $address, $user['id']]);
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['address'] = $address;
            $user = $_SESSION['user'];
            $success_message = "Profile updated successfully!";
        }
        require_once __DIR__ . '/../app/views/patient/profile.php';
        break;

    case 'doctor_dashboard':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') { header("Location: index.php?route=login"); exit; }
        $doctor = $_SESSION['user'];
        $today = date('Y-m-d');
        $stmt = $db->prepare("SELECT * FROM appointments WHERE doctor_name = ? AND appointment_date = ? ORDER BY appointment_time ASC");
        $stmt->execute([$doctor['name'], $today]);
        $appointments = $stmt->fetchAll();

        $monday = date('Y-m-d', strtotime('monday this week'));
        $sunday = date('Y-m-d', strtotime('sunday this week'));
        $stmt_week = $db->prepare("SELECT * FROM appointments WHERE doctor_name = ? AND appointment_date BETWEEN ? AND ?");
        $stmt_week->execute([$doctor['name'], $monday, $sunday]);
        $week_appointments = $stmt_week->fetchAll();

        $stat_today_appts = count($appointments);
        $stat_total_week = count($week_appointments);
        $stat_completed = 0; $stat_cancelled = 0; $stat_pending = 0;
        $daily_counts = ['Mon' => 0, 'Tue' => 0, 'Wed' => 0, 'Thu' => 0, 'Fri' => 0, 'Sat' => 0, 'Sun' => 0];

        foreach ($week_appointments as $wa) {
            $status = $wa['status'] ?? 'Scheduled';
            if ($status === 'Completed') $stat_completed++;
            elseif ($status === 'Cancelled') $stat_cancelled++;
            else $stat_pending++;

            $day_name = date('D', strtotime($wa['appointment_date']));
            if (isset($daily_counts[$day_name])) $daily_counts[$day_name]++;
        }

        $stmt_pat = $db->prepare("SELECT COUNT(DISTINCT patient_name) as total_p FROM appointments WHERE doctor_name = ?");
        $stmt_pat->execute([$doctor['name']]);
        $res_pat = $stmt_pat->fetch();
        $stat_total_patients = $res_pat['total_p'] ?? 0;

        $max_val = max(max($daily_counts), 5);
        $chart_width = 500; $chart_height = 160;
        $x_step = $chart_width / 6;
        $points = []; $i = 0;
        foreach ($daily_counts as $day => $count) {
            $x = $i * $x_step;
            $y = $chart_height - (($count / $max_val) * ($chart_height - 30)) - 10;
            $points[] = "$x,$y";
            $i++;
        }
        $polyline_points = implode(' ', $points);
        $polygon_points = "0,$chart_height " . $polyline_points . " $chart_width,$chart_height";

        require_once __DIR__ . '/../app/views/doctor/doctor_dashboard.php';
        break;

    case 'doctor_appointments':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') { header("Location: index.php?route=login"); exit; }
        $doctor = $_SESSION['user'];
        $stmt = $db->prepare("SELECT * FROM appointments WHERE doctor_name = ? ORDER BY appointment_date DESC, appointment_time DESC");
        $stmt->execute([$doctor['name']]);
        $appointments = $stmt->fetchAll();
        require_once __DIR__ . '/../app/views/doctor/doctor_appointments.php';
        break;

    case 'manage_appointment':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') { header("Location: index.php?route=login"); exit; }
        $doctor = $_SESSION['user'];
        $appointment_id = $_GET['id'] ?? 0;
        $app_s = $db->prepare("SELECT * FROM appointments WHERE id = ?");
        $app_s->execute([$appointment_id]);
        $appointment = $app_s->fetch();
        $success_msg = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $appointment) {
            $up = $db->prepare("UPDATE appointments SET patient_condition = ?, prescription = ?, doctor_advice = ?, status = ? WHERE id = ?");
            $up->execute([trim($_POST['patient_condition']), trim($_POST['prescription']), trim($_POST['doctor_advice']), trim($_POST['status']), $appointment_id]);
            $success_msg = "Patient record and prescription updated successfully!";
            $app_s->execute([$appointment_id]);
            $appointment = $app_s->fetch();
        }
        require_once __DIR__ . '/../app/views/doctor/doctor_manage_appointment.php';
        break;

    case 'medical_records':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'doctor') { header("Location: index.php?route=login"); exit; }
        $doctor = $_SESSION['user'];
        $stmt = $db->prepare("SELECT * FROM appointments WHERE doctor_name = ? AND status = 'Completed' ORDER BY appointment_date DESC");
        $stmt->execute([$doctor['name']]);
        $records = $stmt->fetchAll();
        require_once __DIR__ . '/../app/views/doctor/doctor_medical_records.php';
        break;

    case 'sitter_dashboard':
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'sitter') { header("Location: index.php?route=login"); exit; }
        $sitter = $_SESSION['user'];
        $stmt = $db->prepare("SELECT * FROM sitter_bookings WHERE sitter_name = ? ORDER BY booking_date DESC");
        $stmt->execute([$sitter['name']]);
        $bookings = $stmt->fetchAll();
        require_once __DIR__ . '/../app/views/sitter/sitter_dashboard.php';
        break;

    default:
        http_response_code(404);
        echo "404 Page Not Found";
        break;
}
?>
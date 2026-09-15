<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Telemedicine++</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f7f6; color: #333; }
        header { background: #0056b3; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;}
        header a { color: white; text-decoration: none; margin: 0 10px; font-weight: bold; }
        .container { max-width: 1000px; margin: 2rem auto; padding: 2rem; background: white; box-shadow: 0 4px 8px rgba(0,0,0,0.1); border-radius: 8px;}
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .card { padding: 1.5rem; border: 1px solid #e0e0e0; border-radius: 8px; background: #fff; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);}
        input, select, button { padding: 12px; margin: 8px 0; width: 100%; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;}
        button { background: #0056b3; color: white; border: none; cursor: pointer; font-weight: bold; }
        button:hover { background: #004494; }
        .alert { background: #ffdddd; color: #d8000c; padding: 12px; margin-bottom: 15px; border-radius: 4px; border-left: 5px solid #d8000c;}
        .success { background: #ddffdd; color: #4F8A10; padding: 12px; margin-bottom: 15px; border-radius: 4px; border-left: 5px solid #4F8A10;}
    </style>
</head>
<body>

<header>
    <div style="font-size: 1.5rem;">Telemedicine++</div>
    <nav>
        <a href="index.php">Home</a>
        
        <?php if(isset($_SESSION['user'])): ?>
            <a href="dashboard.php" style="color: #a8d5ff;">[ Dashboard ]</a>
            <a href="logout.php" style="color: #ffb3b3;">Log Out (<?= htmlspecialchars($_SESSION['user']['name']) ?>)</a>
        <?php else: ?>
            <a href="login.php" style="color: #a8d5ff;">Log In</a>
            <a href="signup.php" style="color: #a8d5ff;">Sign Up</a>
        <?php endif; ?>
    </nav>
</header>
<div class="container"></div>
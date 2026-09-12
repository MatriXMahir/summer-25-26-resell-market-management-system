<?php
$oldEmail = $_COOKIE['remember_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login - Resell Market</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container" style="max-width:420px">
    <div class="box">
        <h2>Log In</h2>
        <p>Resell Market Management System</p>

        <?php $flash = flash_get(); if ($flash): ?>
            <div class="alert alert-<?= esc($flash['type']) ?>"><?= esc($flash['message']) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['reason']) && $_GET['reason'] === 'timeout'): ?>
            <div class="alert alert-error">You were logged out after 15 minutes of inactivity.</div>
        <?php elseif (isset($_GET['reason']) && $_GET['reason'] === 'unauthorized'): ?>
            <div class="alert alert-error">You don't have access to that page.</div>
        <?php endif; ?>

        <?php if (!empty($loginErrors)): ?>
            <div class="alert alert-error">
                <?php foreach ($loginErrors as $err) echo esc($err) . "<br>"; ?>
            </div>
        <?php endif; ?>

        <form id="loginForm" method="post" action="index.php?page=login&action=submit"
              onsubmit="return validateForm(this, {
                  email:    [{type:'required', message:'Email is required.'}, {type:'email', message:'Enter a valid email address.'}],
                  password: [{type:'required', message:'Password is required.'}]
              });">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" value="<?= esc($oldEmail) ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="remember" value="1" style="width:auto"> Remember my email on this device</label>
            </div>
            <button class="btn-primary" type="submit">Log In</button>
        </form>

        <p style="margin-top:14px">New here? <a href="index.php?page=register">Create an account</a></p>

        <div class="demo-accounts">
            <b>Demo accounts</b><br>
            Admin &ndash; dhrubo@resell.com / admin123<br>
            Buyer &ndash; mahir@resell.com / pass1234<br>
            Seller &ndash; sanjida@resell.com / pass1234<br>
            Delivery &ndash; tonmoy@resell.com / pass1234
        </div>
    </div>
</div>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>

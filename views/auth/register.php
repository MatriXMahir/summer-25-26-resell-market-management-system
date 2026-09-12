<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign Up - Resell Market</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container" style="max-width:420px">
    <div class="box">
        <h2>Create Your Account</h2>
        <p>Sign up as a Buyer, Seller, or Delivery Man. Admin approval is required before you can log in.</p>

        <?php if (!empty($registerErrors)): ?>
            <div class="alert alert-error">
                <?php foreach ($registerErrors as $err) echo esc($err) . "<br>"; ?>
            </div>
        <?php endif; ?>

        <form id="registerForm" method="post" action="index.php?page=register&action=submit"
              onsubmit="return validateForm(this, {
                  full_name:        [{type:'required', message:'Full name is required.'}],
                  email:            [{type:'required', message:'Email is required.'}, {type:'email', message:'Enter a valid email address.'}],
                  password:         [{type:'required', message:'Password is required.'}, {type:'min', value:8, message:'Password must be at least 8 characters.'}],
                  confirm_password: [{type:'required', message:'Please confirm your password.'}, {type:'match', value:'password', message:'Passwords do not match.'}],
                  role:             [{type:'required', message:'Please choose a role.'}]
              });">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" value="<?= esc($old['full_name']) ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" value="<?= esc($old['email']) ?>">
            </div>
            <div class="form-group">
                <label for="password">Password (min 8 characters)</label>
                <input type="password" id="password" name="password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password">
            </div>
            <div class="form-group">
                <label for="role">I am a...</label>
                <select id="role" name="role">
                    <option value="">-- choose role --</option>
                    <option value="buyer"    <?= $old['role'] === 'buyer'    ? 'selected' : '' ?>>Buyer</option>
                    <option value="seller"   <?= $old['role'] === 'seller'   ? 'selected' : '' ?>>Seller</option>
                    <option value="delivery" <?= $old['role'] === 'delivery' ? 'selected' : '' ?>>Delivery Man</option>
                </select>
            </div>
            <button class="btn-primary" type="submit">Sign Up</button>
        </form>

        <p style="margin-top:14px">Already have an account? <a href="index.php?page=login">Log in</a></p>
    </div>
</div>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>

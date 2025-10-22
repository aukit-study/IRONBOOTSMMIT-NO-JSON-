<?php include 'header.php'; ?>

<div class="form-container">
    <h2>Register</h2>

    <?php
    if (isset($_GET['error'])) {
        $error_msg = "";
        if ($_GET['error'] == 'userexists') {
            $error_msg = "Username นี้มีผู้ใช้งานแล้ว! โปรดเลือก Username ใหม่";
        } elseif ($_GET['error'] == 'inputinvalid') {
             $error_msg = "ข้อมูลไม่ถูกต้องหรือรหัสผ่านสั้นเกินไป (ต้อง 6 ตัวอักษรขึ้นไป)";
        }
        echo '<p style="color: red; text-align: center; margin-bottom: 1rem;">' . htmlspecialchars($error_msg) . '</p>';
    }
    ?>

    <form action="register_process.php" method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="display_name">Display Name:</label>
            <input type="text" id="display_name" name="display_name" required>
        </div>
        <div class="form-group">
            <label for="password">Password (min 6 chars):</label>
            <input type="password" id="password" name="password" required minlength="6">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
        </div>
        <button type="submit" class="btn primary-btn">SIGN UP</button>
    </form>
    <p style="margin-top: 15px;">
        Already have an account? <a href="login.php">Sign in here</a>.
    </p>
</div>

<?php include 'footer.php'; ?>
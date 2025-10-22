<?php include 'header.php'; ?>

<div class="form-container">
    <h2>Login</h2>

    <div class="form-container">
    <h2>Login</h2>

    <?php
    // --- เพิ่มส่วนนี้เข้าไป ---
    if (isset($_GET['success']) && $_GET['success'] == 'registered') {
        echo '<p style="color: green; text-align: center; margin-bottom: 1rem;">สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ</p>';
    }

    // โค้ดแสดง Error เดิม
    if (isset($_GET['error']) && $_GET['error'] == 'wrongcredentials') {
        echo '<p style="color: red; text-align: center; margin-bottom: 1rem;">Username หรือ Password ไม่ถูกต้อง!</p>';
    }
    ?>

    <form action="login_process.php" method="POST">
        ...
    </form>
    </div>

    <?php
    // ตรวจสอบว่ามี error ส่งมาจาก URL หรือไม่   
    if (isset($_GET['error']) && $_GET['error'] == 'wrongcredentials') {
        echo '<p style="color: red; text-align: center; margin-bottom: 1rem;">Username หรือ Password ไม่ถูกต้อง!</p>';
    }
    ?>

    <form action="login_process.php" method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">SIGN IN</button>
    </form>
    <p style="margin-top: 15px;">
        If you have not yet registered, please <a href="register.php">sign up here</a>.
    </p>
</div>

<?php include 'footer.php'; ?>
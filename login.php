<?php
session_start();
require_once 'config/db.php';
$error = "";

if (isset($_POST['login_btn'])) {
    $id_num = trim($_POST['national_id']);
    $pass = strtolower(trim($_POST['password'])); 

    $res = mysqli_query($conn, "SELECT * FROM users WHERE username='$id_num'");
    if ($user = mysqli_fetch_assoc($res)) {
        if (password_verify($pass, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'patient') {
                $p_res = mysqli_query($conn, "SELECT patient_id FROM patients WHERE username='$id_num'");
                $p_data = mysqli_fetch_assoc($p_res);
                $_SESSION['patient_id'] = $p_data['patient_id'];
                header("Location: patient_portal.php");
            } elseif ($user['role'] == 'doctor') {
                header("Location: doctor_dashboard.php");
            } else {
                header("Location: pharmacy_dashboard.php");
            }
            exit();
        } else { $error = "Incorrect ID or First Name."; }
    } else { $error = "ID not found."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Afya Hub | Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body style="background:#f0f2f5; display:flex; align-items:center; justify-content:center; height:100vh;">
    <div style="background:white; padding:30px; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.1); width:350px;">
        <h2 style="text-align:center; color:#1a3a5a;">Afya Poa Hub</h2>
        <?php if($error) echo "<p style='color:red; font-size:0.8rem;'>$error</p>"; ?>
        <?php if(isset($_GET['success'])) echo "<p style='color:green; font-size:0.8rem;'>".$_GET['success']."</p>"; ?>

        <form method="POST">
            <input type="text" name="national_id" placeholder="National ID" required style="width:100%; padding:10px; margin-bottom:15px;">
            <input type="password" name="password" placeholder="First Name (Password)" required style="width:100%; padding:10px; margin-bottom:20px;">
            <button type="submit" name="login_btn" style="width:100%; background:#1a3a5a; color:white; padding:12px; border:none; border-radius:5px; font-weight:bold; cursor:pointer;">Login</button>
        </form>
        <p style="text-align:center; margin-top:15px; font-size:0.9rem;">New to the Hub? <a href="signup.php">Register Now</a></p>
    </div>
</body>
</html>
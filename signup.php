<?php
require_once 'config/db.php';
$message = "";

if (isset($_POST['signup_btn'])) {
    // 1. DATA CLEANING & MANDATORY CHECKS
    $id_number = trim(mysqli_real_escape_string($conn, $_POST['national_id']));
    $full_name = trim(mysqli_real_escape_string($conn, $_POST['full_name']));
    $role = $_POST['role']; // Will be 'doctor' or 'pharmacist'
    $license = trim(mysqli_real_escape_string($conn, $_POST['license_no']));
    $hospital = trim(mysqli_real_escape_string($conn, $_POST['hospital_name']));
    
    // LOGIC: First name (lowercase) is the login password
    $name_parts = explode(" ", $full_name);
    $first_name = strtolower($name_parts[0]); 
    $hashed_password = password_hash($first_name, PASSWORD_DEFAULT);

    // Check if ID already exists
    $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$id_number'");
    if (mysqli_num_rows($check) > 0) {
        $message = "Error: This National ID is already registered.";
    } else {
        // 2. SAVE TO USERS (All fields are now required)
        $has_full_name_col = false;
        $full_name_col_q = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'full_name'");
        if ($full_name_col_q && mysqli_num_rows($full_name_col_q) > 0) {
            $has_full_name_col = true;
        }

        if ($has_full_name_col) {
            $user_sql = "INSERT INTO users (username, full_name, password, role, license_no, hospital_name) 
                         VALUES ('$id_number', '$full_name', '$hashed_password', '$role', '$license', '$hospital')";
        } else {
            $user_sql = "INSERT INTO users (username, password, role, license_no, hospital_name) 
                         VALUES ('$id_number', '$hashed_password', '$role', '$license', '$hospital')";
        }
        
        if (mysqli_query($conn, $user_sql)) {
            header("Location: login.php?success=Professional Account Created! Login with ID: $id_number and Password: $first_name");
            exit();
        } else {
            $message = "Database Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Afya Hub | Professional Registration</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body style="background: #f0f2f5; display: flex; align-items: center; justify-content: center; min-height: 100vh;">

    <div style="background: white; margin-top: 200px; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 450px;">
        <h2 style="text-align: center; color: #1a3a5a; margin-bottom: 5px;">Medical Staff Registry</h2>
        <p style="text-align: center; color: #666; font-size: 0.9rem; margin-bottom: 25px;">Self-registration for Doctors & Pharmacists only</p>

        <?php if($message) echo "<div style='color:red; text-align:center; background:#fee; padding:10px; border-radius:5px; margin-bottom:15px;'>$message</div>"; ?>

        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Professional Role:</label>
                <select name="role" required style="width:100%; padding:12px; border:1px solid #ccc; border-radius:6px;">
                    <option value="" disabled selected>Select your role</option>
                    <option value="doctor">Medical Doctor</option>
                    <option value="pharmacist">Pharmacist</option>
                </select>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Full Official Name:</label>
                <input type="text" name="full_name" required placeholder="e.g. Dr. Jane Smith" style="width:100%; padding:12px; border:1px solid #ccc; border-radius:6px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">National ID / Passport No:</label>
                <input type="text" name="national_id" required placeholder="This will be your login ID" style="width:100%; padding:12px; border:1px solid #ccc; border-radius:6px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">License Number:</label>
                <input type="text" name="license_no" required placeholder="Enter Practice License Number" style="width:100%; padding:12px; border:1px solid #ccc; border-radius:6px;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Facility Name (Hospital/Pharmacy):</label>
                <input type="text" name="hospital_name" required placeholder="Enter Current Facility" style="width:100%; padding:12px; border:1px solid #ccc; border-radius:6px;">
            </div>

            <button type="submit" name="signup_btn" style="width:100%; background:#1a3a5a; color:white; padding:15px; border:none; border-radius:6px; font-weight:bold; cursor:pointer; transition: 0.3s;">
                Register Professional Account
            </button>
            
            <p style="text-align:center; margin-top:20px; font-size:0.9rem;">Already registered? <a href="login.php" style="color:#1a3a5a; font-weight:bold;">Login here</a></p>
        </form>
    </div>

</body>
</html>

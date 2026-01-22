<?php
session_start();
require_once '../config/db.php';

// Security: Only Doctors or Pharmacists can add patients
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'doctor' && $_SESSION['role'] !== 'pharmacist')) {
    header("Location: ../login.php");
    exit();
}

$message = "";

if (isset($_POST['reg_patient_btn'])) {
    // 1. COLLECT AND CLEAN DATA (All fields required)
    $id_number = trim(mysqli_real_escape_string($conn, $_POST['national_id']));
    $full_name = trim(mysqli_real_escape_string($conn, $_POST['full_name']));
    $blood_group = $_POST['blood_group'];
    
    // 2. LOGIC: Extract first name for the password
    $name_parts = explode(" ", $full_name);
    $first_name = strtolower($name_parts[0]); 
    $hashed_password = password_hash($first_name, PASSWORD_DEFAULT);

    // 3. CHECK IF ALREADY REGISTERED
    $check = mysqli_query($conn, "SELECT id FROM users WHERE username='$id_number'");
    if (mysqli_num_rows($check) > 0) {
        $message = "Error: This National ID is already in the system.";
    } else {
        // 4. CREATE LOGIN ACCOUNT (Users Table)
        $sql1 = "INSERT INTO users (username, password, role) VALUES ('$id_number', '$hashed_password', 'patient')";
        
        if (mysqli_query($conn, $sql1)) {
            // 5. CREATE HEALTH PROFILE (Patients Table)
            $sql2 = "INSERT INTO patients (username, full_name, national_id, blood_group) 
                     VALUES ('$id_number', '$full_name', '$id_number', '$blood_group')";
            
            if (mysqli_query($conn, $sql2)) {
                $message = "Success: Citizen Registered! ID: $id_number | Password: $first_name";
            }
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
    <title>Afya Hub | Register Citizen</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body style="background: #f4f7f6; padding: 40px;">

    <div style="max-width: 500px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
        <h2 style="color: #1a3a5a; border-bottom: 2px solid #eee; padding-bottom: 10px;">National Patient Registry</h2>
        <p style="font-size: 0.9rem; color: #666;">Registrar: <?php echo $_SESSION['username']; ?> (<?php echo ucfirst($_SESSION['role']); ?>)</p>

        <?php if($message): ?>
            <div style="background: #e7f3ff; color: #1a3a5a; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 5px solid #1a3a5a;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label style="font-weight:bold;">Full Legal Name:</label>
                <input type="text" name="full_name" required placeholder="First and Last Name" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="font-weight:bold;">National ID / Passport No:</label>
                <input type="text" name="national_id" required placeholder="Required for Identity Link" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="font-weight:bold;">Blood Group:</label>
                <select name="blood_group" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                    <option value="" disabled selected>Select Blood Group</option>
                    <option value="Unknown">Unknown</option>
                    <option value="A+">A+</option><option value="O+">O+</option>
                    <option value="B+">B+</option><option value="AB+">AB+</option>
                    <option value="A-">A-</option><option value="O-">O-</option>
                </select>
            </div>

            <button type="submit" name="reg_patient_btn" style="width:100%; background:#1a3a5a; color:white; padding:15px; border:none; border-radius:5px; font-weight:bold; cursor:pointer;">
                Submit to National Registry
            </button>
            <a href="../doctor_dashboard.php" style="display:block; text-align:center; margin-top:15px; color:#666; text-decoration:none;">Cancel and Go Back</a>
        </form>
    </div>

</body>
</html>
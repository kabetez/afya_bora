<?php
session_start();
require_once '../config/db.php';

// Security: Only allow patients
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

$national_id = $_SESSION['national_id'];
$msg = "";

// Handle Form Submission
if (isset($_POST['update_btn'])) {
    $blood = mysqli_real_escape_string($conn, $_POST['blood_group']);
    $allergies = mysqli_real_escape_string($conn, $_POST['allergies']);
    $conditions = mysqli_real_escape_string($conn, $_POST['conditions']);
    $nok_name = mysqli_real_escape_string($conn, $_POST['nok_name']);
    $nok_phone = mysqli_real_escape_string($conn, $_POST['nok_phone']);

    $update_sql = "UPDATE patients SET 
                   blood_group='$blood', 
                   allergies='$allergies', 
                   chronic_conditions='$conditions', 
                   next_of_kin_name='$nok_name', 
                   next_of_kin_phone='$nok_phone' 
                   WHERE national_id='$national_id'";

    if (mysqli_query($conn, $update_sql)) {
        $msg = "Health Profile Updated Successfully!";
    }
}

// Fetch current data
$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM patients WHERE national_id='$national_id'"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Afya Hub | Vital Bio</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-wrapper">
        <main class="container" style="padding: 40px; max-width: 800px; margin: auto;">
            
            <div class="dashboard-card">
                <h2 style="color: var(--primary-blue);">My Vital Bio</h2>
                <p style="color: #666;">Provide critical information for healthcare providers in case of emergencies.</p>
                <hr>

                <?php if($msg) echo "<p style='color: green; font-weight: bold;'>$msg</p>"; ?>

                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                        <div>
                            <label><b>Blood Group:</b></label>
                            <select name="blood_group" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                                <option value="<?php echo $data['blood_group']; ?>"><?php echo $data['blood_group'] ?: 'Select...'; ?></option>
                                <option value="A+">A+</option><option value="A-">A-</option>
                                <option value="B+">B+</option><option value="B-">B-</option>
                                <option value="O+">O+</option><option value="O-">O-</option>
                                <option value="AB+">AB+</option><option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div>
                            <label><b>Allergies:</b></label>
                            <input type="text" name="allergies" value="<?php echo $data['allergies']; ?>" placeholder="e.g. Penicillin, Nuts" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                        </div>
                    </div>

                    <div style="margin-top: 20px;">
                        <label><b>Chronic Conditions:</b></label>
                        <textarea name="conditions" style="width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ddd;" rows="3"><?php echo $data['chronic_conditions']; ?></textarea>
                    </div>

                    <h4 style="margin-top: 30px; color: var(--accent-maroon);">Emergency Contact (Next of Kin)</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <input type="text" name="nok_name" placeholder="Full Name" value="<?php echo $data['next_of_kin_name']; ?>" style="padding: 10px;">
                        <input type="text" name="nok_phone" placeholder="Phone Number" value="<?php echo $data['next_of_kin_phone']; ?>" style="padding: 10px;">
                    </div>

                    <button type="submit" name="update_btn" style="margin-top: 30px; width: 100%; background: var(--primary-blue); color: white; padding: 15px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">
                        Save To National Registry
                    </button>
                    <a href="../patient_portal.php" style="display: block; text-align: center; margin-top: 15px; text-decoration: none; color: #666;">Back to Dashboard</a>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
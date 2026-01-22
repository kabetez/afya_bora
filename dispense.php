<?php
session_start();
require_once 'config/db.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pharmacist') {
    header("Location: login.php");
    exit();
}

$prescription_id = $_GET['id'] ?? null;
$message = "";

// 2. HANDLE DISPENSE ACTION
if (isset($_POST['confirm_dispense'])) {
    $p_id = $_POST['p_id'];
    $pharmacist_name = $_SESSION['username'];
    
    $update_sql = "UPDATE prescriptions SET status='Dispensed', dispensed_by='$pharmacist_name', dispensed_at=NOW() WHERE id='$p_id'";
    
    if (mysqli_query($conn, $update_sql)) {
        header("Location: pharmacy_dashboard.php?msg=Medication Successfully Dispensed");
        exit();
    } else {
        $message = "Error updating record: " . mysqli_error($conn);
    }
}

// 3. FETCH PRESCRIPTION DETAILS
$query = "SELECT p.*, pt.full_name, pt.national_id, pt.blood_group 
          FROM prescriptions p 
          JOIN patients pt ON p.patient_id = pt.patient_id 
          WHERE p.id = '$prescription_id'";
$res = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($res);

if (!$data) { die("Prescription not found."); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dispense Medication | Afya Hub</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body style="background: #f1f5f9; font-family: sans-serif; padding: 40px;">

    <div style="max-width: 600px; margin: auto; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        
        <div style="background: #10b981; color: white; padding: 25px; text-align: center;">
            <h2 style="margin: 0;">Dispensing Verification</h2>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Verify details before issuing medication</p>
        </div>

        <div style="padding: 30px;">
            <div style="background: #f8fafc; padding: 15px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #e2e8f0;">
                <h4 style="margin: 0 0 10px 0; color: #64748b; font-size: 0.8rem; text-transform: uppercase;">Patient Identity</h4>
                <p style="margin: 0; font-size: 1.2rem; font-weight: bold; color: #1e293b;"><?php echo $data['full_name']; ?></p>
                <p style="margin: 5px 0 0 0; color: #475569;">ID: <?php echo $data['national_id']; ?> | Blood: <span style="color: #ef4444; font-weight: bold;"><?php echo $data['blood_group']; ?></span></p>
            </div>

            <div style="margin-bottom: 30px;">
                <h4 style="color: #64748b; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 10px;">Doctor's Prescription</h4>
                <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 20px; border-radius: 10px;">
                    <p style="margin: 0; font-size: 1.1rem; line-height: 1.6; color: #92400e;">
                        <?php echo nl2br($data['treatment']); ?>
                    </p>
                </div>
                <p style="font-size: 0.85rem; color: #94a3b8; margin-top: 10px;">Prescribed by: <strong>Dr. <?php echo $data['doctor_name']; ?></strong> on <?php echo date('d M Y', strtotime($data['created_at'])); ?></p>
            </div>

            <form method="POST">
                <input type="hidden" name="p_id" value="<?php echo $data['id']; ?>">
                
                <div style="display: flex; gap: 15px;">
                    <a href="pharmacy_dashboard.php" style="flex: 1; text-align: center; padding: 15px; background: #e2e8f0; color: #475569; text-decoration: none; border-radius: 8px; font-weight: bold;">Cancel</a>
                    <button type="submit" name="confirm_dispense" style="flex: 2; background: #10b981; color: white; padding: 15px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 1rem;">
                        Confirm Dispense & Issue 
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
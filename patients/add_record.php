<?php
session_start();
require_once '../config/db.php';

// Security: Only doctors can add national health records
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

// Get the National ID from the URL
if (isset($_GET['id'])) {
    $nat_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Verify the citizen exists
    $check = mysqli_query($conn, "SELECT full_name FROM patients WHERE national_id = '$nat_id'");
    $citizen = mysqli_fetch_assoc($check);
} else {
    header("Location: search.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $facility = mysqli_real_escape_string($conn, $_POST['facility_name']);
    $diagnosis = mysqli_real_escape_string($conn, $_POST['diagnosis']);
    $treatment = mysqli_real_escape_string($conn, $_POST['treatment']);
    $doctor = $_SESSION['username']; // Tracking which doctor logged the entry

    $sql = "INSERT INTO national_health_records (national_id, facility_name, diagnosis, treatment, doctor_name) 
            VALUES ('$nat_id', '$facility', '$diagnosis', '$treatment', '$doctor')";

    if (mysqli_query($conn, $sql)) {
        $message = "<p style='color:green; font-weight:bold;'>Encounter synchronized with National Registry!</p>";
    } else {
        $message = "<p style='color:red;'>Registry Error: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Clinical Encounter | Afya Bora</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">

    <nav class="navbar">
        <div class="nav-container">
            <span class="nav-logo">Afya Bora | National Data Entry</span>
            <ul class="nav-links">
                <li><a href="../doctor_dashboard.php">Dashboard</a></li>
                <li><a href="search.php">Back to Registry</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-card" style="max-width: 700px; margin: 0 auto;">
            <h2 style="color: var(--accent-maroon);">Add Medical Encounter</h2>
            <p>Citizen: <strong><?php echo $citizen['full_name']; ?></strong> | ID: <strong><?php echo $nat_id; ?></strong></p>
            <hr>

            <?php echo $message; ?>

            <form method="POST">
                <label>Treating Facility (Hospital/Clinic Name):</label>
                <input type="text" name="facility_name" placeholder="e.g. Kenyatta National Hospital" required>

                <label style="margin-top:15px; display:block;">Clinical Diagnosis:</label>
                <textarea name="diagnosis" rows="3" placeholder="Describe the medical finding..." required style="width:100%; padding:10px; border-radius:5px; border:1px solid #ddd;"></textarea>

                <label style="margin-top:15px; display:block;">Treatment / Prescription Given:</label>
                <textarea name="treatment" rows="4" placeholder="List medications or procedures performed..." required style="width:100%; padding:10px; border-radius:5px; border:1px solid #ddd;"></textarea>

                <div style="margin-top:20px; background: #f9f9f9; padding:10px; border-radius:5px; font-size:0.8rem;">
                    <p><strong>Registry Note:</strong> This entry will be timestamped and signed by <strong>Dr. <?php echo $_SESSION['username']; ?></strong> as per National Health Guidelines.</p>
                </div>

                <button type="submit" style="margin-top: 20px; background-color: var(--accent-maroon);">Upload to National Database</button>
            </form>
        </div>
    </div>

</body>
</html>

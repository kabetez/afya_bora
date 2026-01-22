<?php
session_start();
require_once '../config/db.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

// Get the Parent ID from the URL
if (isset($_GET['parent_id'])) {
    $parent_id = mysqli_real_escape_string($conn, $_GET['parent_id']);
    
    // Fetch parent name for the UI
    $p_res = mysqli_query($conn, "SELECT full_name, national_id FROM patients WHERE patient_id = '$parent_id'");
    $parent = mysqli_fetch_assoc($p_res);
} else {
    header("Location: search.php");
    exit();
}

$message = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $relationship = mysqli_real_escape_string($conn, $_POST['relationship']);
    $blood_group = mysqli_real_escape_string($conn, $_POST['blood_group']);

    $sql = "INSERT INTO dependents (parent_id, full_name, dob, gender, relationship, blood_group) 
            VALUES ('$parent_id', '$full_name', '$dob', '$gender', '$relationship', '$blood_group')";

    if (mysqli_query($conn, $sql)) {
        $message = "<p style='color:green; font-weight:bold;'>Dependent registered and linked to National ID: " . $parent['national_id'] . "</p>";
    } else {
        $message = "<p style='color:red;'>Error: " . mysqli_error($conn) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Dependent | National Registry</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">

    <nav class="navbar">
        <div class="nav-container">
            <span class="nav-logo">Afya Poa | Registry Update</span>
            <ul class="nav-links">
                <li><a href="../doctor_dashboard.php">Dashboard</a></li>
                <li><a href="view.php?id=<?php echo $parent_id; ?>">Back to Patient File</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-card" style="max-width: 600px; margin: 0 auto;">
            <h2 style="color: var(--primary-blue);">Link Dependent/Minor</h2>
            <p>Registering dependent for: <strong><?php echo $parent['full_name']; ?></strong></p>
            <hr>
            
            <?php echo $message; ?>

            <form method="POST">
                <label>Dependent Full Name:</label>
                <input type="text" name="full_name" placeholder="Name as per birth certificate" required>

                <div style="display: flex; gap: 20px; margin-top: 15px;">
                    <div style="flex: 1;">
                        <label>Date of Birth:</label>
                        <input type="date" name="dob" required>
                    </div>
                    <div style="flex: 1;">
                        <label>Gender:</label>
                        <select name="gender" required style="width:100%; padding:10px; margin-top:5px;">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 20px; margin-top: 15px;">
                    <div style="flex: 1;">
                        <label>Relationship:</label>
                        <select name="relationship" required style="width:100%; padding:10px; margin-top:5px;">
                            <option value="Son">Son</option>
                            <option value="Daughter">Daughter</option>
                            <option value="Spouse">Spouse</option>
                            <option value="Other">Other (Ward)</option>
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label>Blood Group:</label>
                        <select name="blood_group" style="width:100%; padding:10px; margin-top:5px;">
                            <option value="Unknown">Unknown</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="AB+">AB+</option>
                        </select>
                    </div>
                </div>

                <button type="submit" style="margin-top: 30px; background-color: var(--primary-blue);">Confirm Registry Entry</button>
            </form>
        </div>
    </div>

</body>
</html>
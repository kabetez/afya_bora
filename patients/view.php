<?php
session_start();
require_once '../config/db.php'; 

// 1. Security: Only doctors can access these records
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$patient = null;
$error_message = "";

// 2. Fetch Data based on the ID passed from search.php
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    // Query A: Main Patient Details
    $query = "SELECT * FROM patients WHERE patient_id = '$id'";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $patient = mysqli_fetch_assoc($result);
        $p_id = $patient['patient_id'];

        // Query B: Fetch Medical History (National Encounters)
        $history_query = "SELECT * FROM national_health_records WHERE national_id = '{$patient['national_id']}' ORDER BY created_at DESC";
        $history_results = mysqli_query($conn, $history_query);

        // Query C: Fetch Dependents (Minors linked to this ID)
        $dep_query = "SELECT * FROM dependents WHERE parent_id = '$p_id'";
        $dep_results = mysqli_query($conn, $dep_query);
    } else {
        $error_message = "National ID record not found in central registry.";
    }
} else {
    header("Location: search.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>National Health Record | <?php echo $patient ? $patient['full_name'] : 'Error'; ?></title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .section-title { color: var(--primary-blue); border-bottom: 2px solid var(--accent-maroon); padding-bottom: 5px; margin-top: 30px; }
        .data-label { font-weight: bold; color: var(--accent-maroon); width: 150px; display: inline-block; }
        .record-card { background: #fff; padding: 15px; border-radius: 5px; margin-bottom: 10px; border-left: 5px solid #ddd; }
    </style>
</head>
<body class="dashboard-page">

    <nav class="navbar">
        <div class="nav-container">
            <span class="nav-logo">Afya Bora | National Health Record</span>
            <ul class="nav-links">
                <li><a href="../doctor_dashboard.php">Dashboard</a></li>
                <li><a href="search.php">Back to Search</a></li>
                <li><a href="../logout.php" class="logout-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <?php if ($patient): ?>
            <div class="dashboard-card" style="width: 100%; box-sizing: border-box;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 style="margin:0; color: var(--primary-blue);">Citizen Identification</h2>
                    <button onclick="window.print()" style="background: #555; width: auto; padding: 5px 15px;">Print Full Record</button>
                </div>
                <hr>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                    <div>
                        <p><span class="data-label">Full Name:</span> <?php echo $patient['full_name']; ?></p>
                        <p><span class="data-label">National ID:</span> <?php echo $patient['national_id']; ?></p>
                        <p><span class="data-label">Date of Birth:</span> <?php echo $patient['dob'] ?? 'Not Recorded'; ?></p>
                    </div>
                    <div>
                        <p><span class="data-label">County:</span> <?php echo $patient['county'] ?? 'Nairobi'; ?></p>
                        <p><span class="data-label">Blood Group:</span> <strong><?php echo $patient['blood_group']; ?></strong></p>
                        <p><span class="data-label">Phone:</span> <?php echo $patient['phone']; ?></p>
                    </div>
                </div>
            </div>

            <h3 class="section-title">Linked Dependents (Minors & Spouses)</h3>
            <div class="dashboard-card" style="width: 100%; box-sizing: border-box;">
                <?php if (mysqli_num_rows($dep_results) > 0): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="text-align: left; border-bottom: 2px solid #eee;">
                                <th style="padding: 10px;">Dependent Name</th>
                                <th style="padding: 10px;">Relationship</th>
                                <th style="padding: 10px;">Age/DOB</th>
                                <th style="padding: 10px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($dep = mysqli_fetch_assoc($dep_results)): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 10px;"><strong><?php echo $dep['full_name']; ?></strong></td>
                                    <td style="padding: 10px;"><?php echo $dep['relationship']; ?></td>
                                    <td style="padding: 10px;"><?php echo $dep['dob']; ?></td>
                                    <td style="padding: 10px;"><a href="view_dep.php?id=<?php echo $dep['dep_id']; ?>" style="color: var(--primary-blue);">View Health History</a></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #888; font-style: italic;">No dependents linked to this National ID.</p>
                <?php endif; ?>
                <a href="add_dependent.php?parent_id=<?php echo $p_id; ?>" style="display:inline-block; margin-top:10px; color: var(--accent-maroon); font-weight:bold; text-decoration:none;">+ Register New Minor</a>
            </div>

            <h3 class="section-title">National Clinical Encounters</h3>
            <div style="margin-top: 15px;">
                <?php if (mysqli_num_rows($history_results) > 0): ?>
                    <?php while($rec = mysqli_fetch_assoc($history_results)): ?>
                        <div class="record-card">
                            <div style="display: flex; justify-content: space-between;">
                                <strong style="color: var(--primary-blue);"><?php echo $rec['facility_name']; ?></strong>
                                <small style="color: #777;"><?php echo date('M d, Y', strtotime($rec['created_at'])); ?></small>
                            </div>
                            <p style="margin: 5px 0;"><strong>Diagnosis:</strong> <?php echo $rec['diagnosis']; ?></p>
                            <p style="margin: 5px 0; font-size: 0.9rem; color: #555;"><strong>Treatment:</strong> <?php echo $rec['treatment']; ?></p>
                            <p style="margin: 5px 0; font-size: 0.8rem; text-align: right; font-style: italic;">Attending: Dr. <?php echo $rec['doctor_name']; ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="dashboard-card" style="width: 100%; text-align: center; color: #888;">
                        No previous medical encounters found for this citizen.
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin: 40px 0; text-align: center;">
                <a href="add_record.php?id=<?php echo $patient['national_id']; ?>" 
                   style="background: var(--accent-maroon); color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                   ADD NEW MEDICAL ENCOUNTER
                </a>
            </div>

        <?php else: ?>
            <div class="error-box"><?php echo $error_message; ?></div>
            <a href="search.php">Return to Registry</a>
        <?php endif; ?>
    </div>

</body>
</html>

<?php
session_start();
require_once 'config/db.php';

// 1. Security Check: Ensure user is logged in AND is a doctor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php?error=Access Denied");
    exit();
}

// KMPDC license
$user_id = $_SESSION['user_id'];
$doctor_name = $_SESSION['username'];
$doctor_data = ['license_no' => 'N/A'];

$has_full_name_col = false;
$full_name_col_q = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'full_name'");
if ($full_name_col_q && mysqli_num_rows($full_name_col_q) > 0) {
    $has_full_name_col = true;
}

if ($has_full_name_col) {
    $doctor_q = mysqli_query($conn, "SELECT license_no, full_name FROM users WHERE id = '$user_id' LIMIT 1");
} else {
    $doctor_q = mysqli_query($conn, "SELECT license_no FROM users WHERE id = '$user_id' LIMIT 1");
}

if ($doctor_q && mysqli_num_rows($doctor_q) > 0) {
    $doctor_data = mysqli_fetch_assoc($doctor_q);
    if ($has_full_name_col && !empty($doctor_data['full_name'])) {
        $doctor_name = $doctor_data['full_name'];
    }
}

// 2. Fetch Live Stats for the National System
// Count total citizens in the registry
$total_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM patients");
$total_citizens = mysqli_fetch_assoc($total_q)['total'];

// Count total medical encounters logged across the country
$records_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM national_health_records");
$total_records = mysqli_fetch_assoc($records_q)['total'];

// Count patients by County for the analytics table
$county_sql = "SELECT county, COUNT(*) as count FROM patients GROUP BY county ORDER BY count DESC LIMIT 5";
$county_result = mysqli_query($conn, $county_sql);

if (isset($_POST['save_record_btn'])) {
    $p_id = $_POST['patient_id'];
    $diag = mysqli_real_escape_string($conn, $_POST['diagnosis']);
    $treat = mysqli_real_escape_string($conn, $_POST['treatment']);
    $doc = mysqli_real_escape_string($conn, $doctor_name);

    // This sends the data to the prescriptions table for the pharmacist
    $sql = "INSERT INTO prescriptions (patient_id, doctor_name, diagnosis, treatment, status) 
            VALUES ('$p_id', '$doc', '$diag', '$treat', 'Pending')";
    
    mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afya Bora | National Health Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="dashboard-page">

    <nav class="navbar">
        <div class="nav-container">
            <a href="login.php" class="nav-logo">Afya <span>Poa</span></a>
            
            <ul class="nav-links">
                <li><a href="doctor_dashboard.php">Dashboard</a></li>
                <li><a href="patients/search.php">Search Registry</a></li>
                <li><a href="patients/add.php">New Registration</a></li>
                <li><a href="logout.php" class="logout-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <h4 style="border-bottom: 1px solid #ffffff55; padding-bottom: 10px; margin-top: 0;">Registry Tools</h4>
            <a href="doctor_dashboard.php" class="sidebar-link active"> System Home</a>
            <a href="patients/search.php" class="sidebar-link"> Search Patient</a>
            <a href="patients/add.php" class="sidebar-link"> Register Citizen</a>
           
            
            <div style="margin-top: auto; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 8px; font-size: 0.8rem;">
                <p style="margin: 0; color: #ffcccc;">Status: Verified Practitioner</p>
                <p style="margin: 5px 0 0 0; color: white;">ID: <?php echo $doctor_data['license_no']; ?></p>
            </div>
        </aside>

        <main class="container" style="padding: 25px; flex: 1; max-width: none; margin: 0;">
            
            <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 25px; border-left: 10px solid var(--accent-maroon); box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h1 style="margin:0; color: var(--primary-blue);">National Health Intelligence</h1>
                <p style="color: #666; margin-top: 5px; text-align: justify;">Welcome, <strong>Dr. <?php echo htmlspecialchars($doctor_name); ?></strong>. License: <?php echo htmlspecialchars($doctor_data['license_no'] ?? 'N/A'); ?> | <span style="color: green;">● Server Active</span></p>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo number_format($total_citizens); ?></h3>
                    <p>Total Citizens Registered</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo number_format($total_records); ?></h3>
                    <p>Clinical Encounters</p>
                </div>
                <div class="stat-card">
                    <h3 style="color: var(--accent-maroon);">47</h3>
                    <p>Counties Synced</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 20px; margin-top: 25px;">
                
                <div class="dashboard-card" style="margin: 0;">
                    <h3 style="color: var(--primary-blue); margin-top: 0;">Registry Operations</h3>
                    <p style="color: #555; line-height: 1.6;">You are currently accessing the central health data nodes. All clinical encounters logged here are part of the National Electronic Health Record (NEHR).</p>
                    
                    <div style="display: flex; justify-content: space-between; gap: 15px; margin-top: 20px;">
                        <a href="patients/search.php" style="background: var(--primary-blue); color: white; padding: 10px 16px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 0.9rem; flex: 0 0 auto; text-align: center; transition: 0.3s;">
                            Open Central Search
                        </a>
                        <a href="patients/add.php" style="background: var(--accent-maroon); color: white; padding: 10px 16px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 0.9rem; flex: 0 0 auto; text-align: center; transition: 0.3s;">
                            Register New Citizen
                        </a>
                    </div>
                </div>

                <div class="dashboard-card" style="margin: 0;">
                    <h4 style="margin-top: 0; color: var(--accent-maroon); border-bottom: 1px solid #eee; padding-bottom: 10px;">Top Registry Localities</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <thead>
                            <tr style="text-align: left; color: #777; border-bottom: 1px solid #eee;">
                                <th style="padding: 10px 0;">County</th>
                                <th style="padding: 10px 0; text-align: right;">Citizens</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($county_result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($county_result)): ?>
                                <tr style="border-bottom: 1px solid #f9f9f9;">
                                    <td style="padding: 12px 0; color: #333;"><?php echo $row['county'] ?: 'Unassigned'; ?></td>
                                    <td style="padding: 12px 0; text-align: right; font-weight: bold; color: var(--primary-blue);">
                                        <?php echo number_format($row['count']); ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" style="padding: 20px; text-align: center; color: #999;">No geographic data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

</body>
</html>


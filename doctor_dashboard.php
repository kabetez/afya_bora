<?php
session_start();
require_once 'config/db.php';

// 1. SECURITY & SESSION CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php?error=Unauthorized Access");
    exit();
}

$u_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// 2. FETCH DOCTOR PROFILE INFO
$doc_query = "SELECT license_no, specialty, hospital_name FROM users WHERE id = '$u_id'";
$doc_res = mysqli_query($conn, $doc_query);
$doc = mysqli_fetch_assoc($doc_res);


// 3. STATS COUNTERS
$total_p = 0;
$total_r = 0;
$p_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM patients");
if($p_count_res) { $total_p = mysqli_fetch_assoc($p_count_res)['total']; }

$r_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM national_health_records");
if($r_count_res) { $total_r = mysqli_fetch_assoc($r_count_res)['total']; }

// 4. FETCH PENDING APPOINTMENTS (Safety Logic added here)
$appointments_res = null; 
$app_sql = "SELECT a.id as app_id, a.app_date, a.reason, p.full_name, p.national_id 
            FROM appointments a 
            JOIN patients p ON a.patient_id = p.patient_id 
            WHERE a.doctor_id = '$u_id' AND a.status = 'Pending'
            ORDER BY a.app_date ASC";

$appointments_res = mysqli_query($conn, $app_sql);

// 5. FETCH RECENT NATIONAL RECORDS
$recent_records = mysqli_query($conn, "SELECT * FROM national_health_records ORDER BY created_at DESC LIMIT 5");
?>
<?php if (isset($_GET['msg'])): ?>
    <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
        ✅ <?php echo htmlspecialchars($_GET['msg']); ?>
    </div>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Afya Hub | Medical Practitioner Portal</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="dashboard-page">

    <nav class="navbar">
        <div class="nav-container">
            <a href="doctor_dashboard.php" class="nav-logo">Afya <span>Hub</span></a>
            <ul class="nav-links">
                <li><a href="doctor_dashboard.php" class="active">Dashboard</a></li>
                <li><a href="patients/add.php">Register Citizen</a></li>
                <li><a href="logout.php" class="logout-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <h4 style="color: #bdc3c7; border-bottom: 1px solid #444; padding-bottom: 10px;">Clinical Tools</h4>
            <a href="doctor_dashboard.php" class="sidebar-link active">🏠 Overview</a>
            <a href="patients/search.php" class="sidebar-link">🔍 Patient Search</a>
            <a href="add_record.php" class="sidebar-link">✍️ Write Prescription</a>
            
            <div style="margin-top: auto; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 8px;">
                <p style="margin: 0; color: #888; font-size: 0.8rem;">Practitioner:</p>
                <p style="margin: 5px 0 0 0; color: white; font-weight: bold;">Dr. <?php echo $username; ?></p>
                <p style="margin: 3px 0 0 0; color: #3498db; font-size: 0.75rem;"><?php echo $doc['specialty'] ?? 'General Practitioner'; ?></p>
                <p style="margin: 3px 0 0 0; color: #999; font-size: 0.7rem;"><?php echo $doc['hospital_name'] ?? 'National Hospital'; ?></p>
            </div>
        </aside>

        <main style="flex: 1; padding: 25px; background: #f4f7f6; overflow-y: auto;">
            
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 25px; border-left: 10px solid #1a3a5a; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h2 style="margin:0; color: #1a3a5a;">Clinical Command Centre</h2>
                <p style="color: #666; margin-top: 5px;">License: <strong><?php echo $doc['license_no'] ?? 'N/A'; ?></strong> | Status: <span style="color: green;">● Online</span></p>
            </div>

            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="stat-card" style="background: white; padding: 20px; border-radius: 10px; border-bottom: 4px solid #3498db;">
                    <h3 style="margin:0; font-size: 2rem;"><?php echo $total_p; ?></h3>
                    <p style="margin:5px 0 0 0; color: #777;">Registered Citizens</p>
                </div>
                <div class="stat-card" style="background: white; padding: 20px; border-radius: 10px; border-bottom: 4px solid #e74c3c;">
                    <h3 style="margin:0; font-size: 2rem;"><?php echo $total_r; ?></h3>
                    <p style="margin:5px 0 0 0; color: #777;">National Records</p>
                </div>
                <div class="stat-card" style="background: white; padding: 20px; border-radius: 10px; border-bottom: 4px solid #f1c40f;">
                    <h3 style="margin:0; font-size: 2rem;">
                        <?php echo ($appointments_res) ? mysqli_num_rows($appointments_res) : 0; ?>
                    </h3>
                    <p style="margin:5px 0 0 0; color: #777;">Pending Visits</p>
                </div>
            </div>

            <div class="dashboard-card" style="margin-top: 25px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3 style="color: #1a3a5a; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0;">📅 Physical Visit Requests</h3>
                <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                    <thead>
                        <tr style="text-align: left; background: #f8f9fa;">
                            <th style="padding: 12px;">Citizen Name</th>
                            <th style="padding: 12px;">Date</th>
                            <th style="padding: 12px;">Reason</th>
                            <th style="padding: 12px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($appointments_res && mysqli_num_rows($appointments_res) > 0): ?>
                            <?php while($app = mysqli_fetch_assoc($appointments_res)): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;">
                                    <strong><?php echo $app['full_name']; ?></strong><br>
                                    <small style="color: #888;">ID: <?php echo $app['national_id']; ?></small>
                                </td>
                                <td style="padding: 12px;"><?php echo date('d M Y', strtotime($app['app_date'])); ?></td>
                                <td style="padding: 12px;"><?php echo $app['reason']; ?></td>
                                <td style="padding: 12px;">
                                    <a href="manage_app.php?id=<?php echo $app['app_id']; ?>&status=Confirmed" style="color: green; text-decoration: none; font-weight: bold;">Accept</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="padding: 20px; text-align: center; color: #999;">No visit requests found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>

        </main>
    </div>
</body>
</html>
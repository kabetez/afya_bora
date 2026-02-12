<?php
session_start();
require_once 'config/db.php';

// 1. SECURITY: Only allow pharmacists
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pharmacist') {
    header("Location: login.php?error=Access Denied");
    exit();
}

$pharmacist = $_SESSION['username'] ?? 'Pharmacist';

// 2. HANDLE DISPENSE ACTION
if (isset($_GET['id'])) {
    $record_id = (int)$_GET['id'];

    if ($record_id > 0) {
        $record_sql = "SELECT r.record_id, r.treatment, r.doctor_name, r.national_id, p.full_name
                       FROM national_health_records r
                       JOIN patients p ON r.national_id = p.national_id
                       WHERE r.record_id = $record_id
                       LIMIT 1";
        $record_res = mysqli_query($conn, $record_sql);

        if ($record_res && ($record = mysqli_fetch_assoc($record_res))) {
            $national_id = mysqli_real_escape_string($conn, $record['national_id']);
            $full_name = mysqli_real_escape_string($conn, $record['full_name']);
            $treatment = mysqli_real_escape_string($conn, $record['treatment']);
            $doctor_name = mysqli_real_escape_string($conn, $record['doctor_name'] ?? 'System');
            $dispensed_by = mysqli_real_escape_string($conn, $pharmacist);

            mysqli_begin_transaction($conn);

            $insert_sql = "INSERT INTO dispense_history (record_id, national_id, patient_name, treatment, doctor_name, dispensed_by, dispensed_at)
                           VALUES ($record_id, '$national_id', '$full_name', '$treatment', '$doctor_name', '$dispensed_by', NOW())";
            $update_sql = "UPDATE national_health_records SET status='Dispensed' WHERE record_id=$record_id";

            $ok1 = mysqli_query($conn, $insert_sql);
            $ok2 = mysqli_query($conn, $update_sql);

            if ($ok1 && $ok2) {
                mysqli_commit($conn);
                header("Location: dispense.php?msg=" . urlencode('Prescription dispensed successfully.'));
                exit();
            }

            mysqli_rollback($conn);
            header("Location: dispense.php?error=" . urlencode('Failed to dispense prescription.'));
            exit();
        }

        header("Location: dispense.php?error=" . urlencode('Prescription not found.'));
        exit();
    }

    header("Location: dispense.php?error=" . urlencode('Invalid prescription ID.'));
    exit();
}

// 3. FETCH PENDING PRESCRIPTIONS
$prescriptions_sql = "SELECT r.*, p.full_name, p.national_id
                      FROM national_health_records r
                      JOIN patients p ON r.national_id = p.national_id
                      WHERE r.status = 'Pending'
                      ORDER BY r.created_at DESC LIMIT 10";

$prescriptions_res = mysqli_query($conn, $prescriptions_sql);

// 4. STATS: Total pending
$total_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM national_health_records WHERE status='Pending'");
$total_pending = mysqli_fetch_assoc($total_q)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Afya Bora | Pharmacy Portal</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="dashboard-page">

    <nav class="navbar">
        <div class="nav-container">
            <a href="pharmacy_dashboard.php" class="nav-logo">Afya <span>Poa</span></a>
            <ul class="nav-links">
                <li><a href="pharmacy_dashboard.php">Pharmacy Home</a></li>
                <li><a href="logout.php" class="logout-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <aside class="sidebar" style="background-color: #004d40;">
            <h4 style="color: #b2dfdb; border-bottom: 1px solid #4db6ac; padding-bottom: 10px; margin-top:0;">Pharmacy Tools</h4>
            <a href="pharmacy_dashboard.php" class="sidebar-link active">Dispensing Queue</a>
            <a href="search_patient.php" class="sidebar-link">Verify ID</a>\n            <a href="dispense_history.php" class="sidebar-link">Dispense History</a>
            <div style="margin-top: auto; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 8px;">
                <p style="margin: 0; color: #b2dfdb; font-size: 0.8rem;">Authenticated As:</p>
                <p style="margin: 5px 0 0 0; color: white; font-weight: bold;">Pharm. <?php echo htmlspecialchars($pharmacist); ?></p>
            </div>
        </aside>

        <main style="flex: 1; padding: 20px; background-color: #f8f9fa; overflow-y: auto;">
            
            <?php if (isset($_GET['msg'])): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #a7f3d0; font-weight: bold;">
                    ✅ <?php echo htmlspecialchars($_GET['msg']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #fecaca; font-weight: bold;">
                    ❌ <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 25px; border-left: 10px solid #00796b; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h1 style="margin:0; color: #004d40;">National Pharmacy Portal</h1>
                <p style="color: #666; margin-top: 5px;">Secure Terminal | <span style="color: green;">● Online</span></p>
            </div>

            <div class="stats-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="stat-card" style="background: white; padding: 20px; border-radius: 10px; border-bottom: 4px solid #00796b; text-align: center;">
                    <h3 style="font-size: 2rem; margin: 0;"><?php echo $total_pending; ?></h3>
                    <p style="color: #666; margin: 5px 0 0 0;">Pending Prescriptions</p>
                </div>
                <div class="stat-card" style="background: white; padding: 20px; border-radius: 10px; text-align: center;">
                    <h3 style="color: #00796b; font-size: 2rem; margin: 0;">Verified</h3>
                    <p style="color: #666; margin: 5px 0 0 0;">System Connection</p>
                </div>
            </div>

            <div class="dashboard-card" style="margin-top: 25px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h3 style="color: #004d40; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0;">National Dispensing Queue</h3>
                
                <div style="overflow-x: auto; width: 100%;"> 
                    <table style="width: 100%; border-collapse: collapse; margin-top: 15px; min-width: 800px;">
                        <thead>
                            <tr style="text-align: left; background: #f4f7f6; color: #555;">
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Citizen Name</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">National ID</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Prescription</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($prescriptions_res && mysqli_num_rows($prescriptions_res) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($prescriptions_res)): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 15px; font-weight: 500;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td style="padding: 15px; font-family: monospace;"><?php echo htmlspecialchars($row['national_id']); ?></td>
                                    <td style="padding: 15px; color: #c00; font-weight: bold;"><?php echo htmlspecialchars($row['treatment']); ?></td>
                                    <td style="padding: 15px;">
                                        <a href="dispense.php?id=<?php echo $row['record_id']; ?>" 
                                           style="display: inline-block; background: #00796b; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 0.85rem;">
                                           Dispense 
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="padding: 30px; text-align: center; color: #999;">No pending prescriptions found.</td>
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



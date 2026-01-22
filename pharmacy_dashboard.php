<?php
session_start();
require_once 'config/db.php';

// Only allow pharmacists
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pharmacist') {
    header("Location: login.php?error=Access Denied");
    exit();
}

// 2. DATA: Fetch prescriptions for the table
$prescriptions_sql = "SELECT r.*, p.full_name, p.national_id 
                      FROM national_health_records r
                      JOIN patients p ON r.national_id = p.national_id 
                      ORDER BY r.created_at DESC LIMIT 10";
$prescriptions_res = mysqli_query($conn, $prescriptions_sql);

// 3. STATS: Total count
$total_q = mysqli_query($conn, "SELECT COUNT(*) as total FROM national_health_records");
$total_dispensed = mysqli_fetch_assoc($total_q)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Afya Poa | Pharmacy Portal</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="dashboard-page">

    <body class="dashboard-page">

    <nav class="navbar">
        <div class="nav-container">
            <a href="pharmacy_dashboard.php" class="nav-logo">Afya <span>Poa</span></a>
            <ul class="nav-links">
                <li><a href="pharmacy_dashboard.php">Pharmacy Home</a></li>
                <li><a href="patients/search.php">Verify ID</a></li>
                <li><a href="logout.php" class="logout-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <aside class="sidebar" style="background-color: #004d40;">
            <h4 style="color: #b2dfdb; border-bottom: 1px solid #4db6ac; padding-bottom: 10px; margin-top:0;">Pharmacy Tools</h4>
            <a href="pharmacy_dashboard.php" class="sidebar-link active"> Dispensing Queue</a>
            <a href="patients/search.php" class="sidebar-link"> Search Patient</a>
            <a href="patients/add.php" class="sidebar-link"> Registration Citizen</a>
            <a href="#" class="sidebar-link">📜 Dispense History</a>
            
            <div style="margin-top: auto; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 8px;">
                <p style="margin: 0; color: #b2dfdb; font-size: 0.8rem;">Logged in as:</p>
                <p style="margin: 5px 0 0 0; color: white; font-weight: bold;">Pharm. <?php echo $_SESSION['username']; ?></p>
            </div>
        </aside>

        <main style="flex: 1; padding: 25px; background-color: #f8f9fa; overflow-y: auto;">
            
            <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 25px; border-left: 10px solid #00796b; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h1 style="margin:0; color: #004d40;">National Pharmacy Portal</h1>
                <p style="color: #666; margin-top: 5px;">Authenticated Terminal | <span style="color: green;">● Live Connection</span></p>
            </div>

            <div class="stats-grid">
                <div class="stat-card" style="border-bottom: 4px solid #00796b;">
                    <h3><?php echo $total_dispensed; ?></h3>
                    <p>Prescriptions Synced</p>
                </div>
                <div class="stat-card">
                    <h3 style="color: #00796b;">Active</h3>
                    <p>PPB Registry Status</p>
                </div>
            </div>

            <div class="dashboard-card" style="margin-top: 25px; padding: 20px;">
                <h3 style="color: #004d40; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0;">National Dispensing Queue</h3>
                <div style="overflow-x: auto;"> <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                        <thead>
                            <tr style="text-align: left; background: #f4f7f6; color: #555;">
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Citizen Name</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">National ID</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Prescription</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Doctor</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($prescriptions_res) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($prescriptions_res)): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 15px; font-weight: 500;"><?php echo $row['full_name']; ?></td>
                                    <td style="padding: 15px; font-family: monospace;"><?php echo $row['national_id']; ?></td>
                                    <td style="padding: 15px; color: #c00; font-weight: bold;"><?php echo $row['treatment']; ?></td>
                                    <td style="padding: 15px;">Dr. <?php echo $row['doctor_name'] ?? 'System'; ?></td>
                                    <td style="padding: 15px;">
                                        <button style="background: #00796b; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; font-weight: bold;">Dispense</button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="padding: 30px; text-align: center; color: #999;">No pending prescriptions found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</body>
</html>
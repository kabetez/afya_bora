<?php
session_start();
require_once 'config/db.php';

// 1. SECURITY: Only allow pharmacists
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pharmacist') {
    header("Location: login.php?error=Access Denied");
    exit();
}

$pharmacist = $_SESSION['username'] ?? 'Pharmacist';

// 2. SEARCH FILTERS
$search_name = trim($_GET['name'] ?? '');
$search_national_id = trim($_GET['national_id'] ?? '');
$search_date_from = trim($_GET['date_from'] ?? '');
$search_date_to = trim($_GET['date_to'] ?? '');

$where = [];
if ($search_name !== '') {
    $safe_name = mysqli_real_escape_string($conn, $search_name);
    $where[] = "patient_name LIKE '%$safe_name%'";
}
if ($search_national_id !== '') {
    $safe_id = mysqli_real_escape_string($conn, $search_national_id);
    $where[] = "national_id LIKE '%$safe_id%'";
}
if ($search_date_from !== '') {
    $safe_from = mysqli_real_escape_string($conn, $search_date_from);
    $where[] = "DATE(dispensed_at) >= '$safe_from'";
}
if ($search_date_to !== '') {
    $safe_to = mysqli_real_escape_string($conn, $search_date_to);
    $where[] = "DATE(dispensed_at) <= '$safe_to'";
}

$where_sql = '';
if (!empty($where)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

// 3. FETCH HISTORY
$history_sql = "SELECT id, record_id, national_id, patient_name, treatment, doctor_name, dispensed_by, dispensed_at
                FROM dispense_history
                $where_sql
                ORDER BY dispensed_at DESC
                LIMIT 50";
$history_res = mysqli_query($conn, $history_sql);

// 4. TOTAL COUNT (filtered)
$count_sql = "SELECT COUNT(*) as total FROM dispense_history $where_sql";
$count_res = mysqli_query($conn, $count_sql);
$total_history = mysqli_fetch_assoc($count_res)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Afya Bora | Dispense History</title>
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
            <a href="pharmacy_dashboard.php" class="sidebar-link">Dispensing Queue</a>
            <a href="patients/search.php" class="sidebar-link">Search Patient</a>
            <a href="patients/add.php" class="sidebar-link">Registration Citizen</a>
            <a href="dispense_history.php" class="sidebar-link active">Dispense History</a>
            
            <div style="margin-top: auto; padding: 15px; background: rgba(0,0,0,0.2); border-radius: 8px;">
                <p style="margin: 0; color: #b2dfdb; font-size: 0.8rem;">Logged in as:</p>
                <p style="margin: 5px 0 0 0; color: white; font-weight: bold;">Pharm. <?php echo htmlspecialchars($pharmacist); ?></p>
            </div>
        </aside>

        <main style="flex: 1; padding: 20px; background-color: #f8f9fa; overflow-y: auto;">
            <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 25px; border-left: 10px solid #00796b; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h1 style="margin:0; color: #004d40;">Dispense History</h1>
                <p style="color: #666; margin-top: 5px;">Total Records: <strong><?php echo (int)$total_history; ?></strong></p>
            </div>

            <div class="dashboard-card" style="margin-bottom: 20px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h3 style="color: #004d40; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0;">Search Filters</h3>
                <form method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; align-items: end;">
                    <div>
                        <label style="display: block; margin-bottom: 6px; color: #555;">Patient Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($search_name); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 6px; color: #555;">National ID</label>
                        <input type="text" name="national_id" value="<?php echo htmlspecialchars($search_national_id); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 6px; color: #555;">Date From</label>
                        <input type="date" name="date_from" value="<?php echo htmlspecialchars($search_date_from); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 6px; color: #555;">Date To</label>
                        <input type="date" name="date_to" value="<?php echo htmlspecialchars($search_date_to); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" style="background: #00796b; color: white; border: none; padding: 10px 16px; border-radius: 6px; font-weight: bold; cursor: pointer;">Search</button>
                        <a href="dispense_history.php" style="display: inline-block; padding: 10px 16px; border-radius: 6px; border: 1px solid #ddd; text-decoration: none; color: #444;">Reset</a>
                    </div>
                </form>
            </div>

            <div class="dashboard-card" style="margin-top: 0; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <h3 style="color: #004d40; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 0;">Dispense Records</h3>
                
                <div style="overflow-x: auto; width: 100%;"> 
                    <table style="width: 100%; border-collapse: collapse; margin-top: 15px; min-width: 900px;">
                        <thead>
                            <tr style="text-align: left; background: #f4f7f6; color: #555;">
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Patient</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">National ID</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Prescription</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Doctor</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Dispensed By</th>
                                <th style="padding: 12px; border-bottom: 2px solid #ddd;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($history_res && mysqli_num_rows($history_res) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($history_res)): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 15px; font-weight: 500;"><?php echo htmlspecialchars($row['patient_name']); ?></td>
                                    <td style="padding: 15px; font-family: monospace;"><?php echo htmlspecialchars($row['national_id']); ?></td>
                                    <td style="padding: 15px; color: #c00; font-weight: bold;"><?php echo htmlspecialchars($row['treatment']); ?></td>
                                    <td style="padding: 15px;"><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                                    <td style="padding: 15px;"><?php echo htmlspecialchars($row['dispensed_by']); ?></td>
                                    <td style="padding: 15px;"><?php echo htmlspecialchars($row['dispensed_at']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="padding: 30px; text-align: center; color: #999;">No dispense history found.</td>
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


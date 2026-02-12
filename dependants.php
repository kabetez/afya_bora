<?php
session_start();
require_once 'config/db.php';

// 1. SECURITY: Only allow patients
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php?error=Access Denied");
    exit();
}

$national_id = $_SESSION['username'];

// 2. Fetch Patient Personal Info
$patient_q = mysqli_query($conn, "SELECT * FROM patients WHERE national_id = '$national_id'");
$patient = mysqli_fetch_assoc($patient_q);

if (!$patient) {
    header("Location: login.php?error=Patient not found");
    exit();
}

// 3. Fetch Dependents
$patient_id = $patient['patient_id'];
$dep_q = mysqli_query($conn, "SELECT * FROM dependents WHERE parent_id = '$patient_id' ORDER BY full_name ASC");
$dependents = [];
if ($dep_q) {
    while ($row = mysqli_fetch_assoc($dep_q)) {
        $dependents[] = $row;
    }
}

// 4. Check for dependent history table (optional)
$history_table_exists = false;
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'dependent_health_records'");
if ($table_check && mysqli_num_rows($table_check) > 0) {
    $history_table_exists = true;
}

function dep_history($conn, $dep_id, $history_table_exists) {
    if (!$history_table_exists) {
        return [];
    }

    $dep_id = (int)$dep_id;
    $history_sql = "SELECT * FROM dependent_health_records WHERE dep_id = $dep_id ORDER BY created_at DESC";
    $history_res = mysqli_query($conn, $history_sql);

    $history = [];
    if ($history_res) {
        while ($rec = mysqli_fetch_assoc($history_res)) {
            $history[] = $rec;
        }
    }
    return $history;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afya Bora | Dependants</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        html, body { max-width: 100%; overflow-x: hidden; }
        .dashboard-wrapper { width: 100%; max-width: 100%; overflow-x: hidden; }
        .dependants-main { min-width: 0; }
        .dep-card { background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); border-left: 6px solid var(--primary-blue); margin-bottom: 20px; }
        .dep-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px; }
        .dep-meta { color: #51636d; font-size: 0.9rem; margin: 6px 0; }
        .dep-label { color: #7a8b94; font-weight: 600; }
        .history-card { background: #f8fafc; padding: 14px; border-radius: 10px; border: 1px solid #e2e8f0; margin-top: 12px; }
        .history-row { display: flex; justify-content: space-between; gap: 12px; align-items: baseline; }
        .empty-note { color: #9aa3a6; font-style: italic; }
        @media (max-width: 1024px) {
            .dashboard-wrapper { flex-direction: column; }
            .sidebar { width: 100% !important; }
            .dependants-main { padding: 20px !important; }
        }
    </style>
</head>
<body class="dashboard-page">

    <nav class="navbar" style="background: var(--primary-blue); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; color: white;">
        <a href="patient_portal.php" class="nav-logo" style="font-size: 1.5rem; font-weight: 700; color: white; text-decoration: none;">Afya <span style="color: #b2dfdb;">Poa</span></a>
        <div style="display: flex; gap: 20px; align-items: center;">
            <span style="font-size: 0.9rem; color: #d9efea;">Welcome, <?php echo explode(' ', $patient['full_name'])[0]; ?></span>
            <a href="logout.php" style="background: var(--accent-maroon); color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Logout</a>
        </div>
    </nav>

    <div class="dashboard-wrapper" style="display: flex; min-height: calc(100vh - 70px);">
        <aside class="sidebar" style="width: 260px; background: var(--primary-blue); padding: 25px; display: flex; flex-direction: column;">
            <h4 style="color: #b2dfdb; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; margin-bottom: 20px;">Main Menu</h4>
            <a href="patient_portal.php" class="sidebar-link"> Health Timeline</a>
            <a href="dependants.php" class="sidebar-link active"> Dependents</a>
            

            <div style="margin-top: auto; padding: 15px; background: rgba(0, 0, 0, 0.2); border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.15);">
                <p style="margin: 0; color: #b2dfdb; font-size: 0.75rem;">Account Holder</p>
                <p style="margin: 4px 0 0 0; color: white; font-weight: 600; font-size: 0.9rem;"><?php echo $patient['full_name']; ?></p>
            </div>
        </aside>

        <main class="dependants-main" style="flex: 1; padding: 40px; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <h1 style="margin: 0; color: var(--primary-blue); font-size: 1.8rem;">Family Dependants</h1>
                    <p style="color: #6b7280; margin-top: 5px;">Linked to National ID: <strong><?php echo $national_id; ?></strong></p>
                </div>
                <div style="color: #2e7d32; font-weight: 600;">Registry Active</div>
            </div>

            <?php if (count($dependents) > 0): ?>
                <?php foreach ($dependents as $dep): ?>
                    <div class="dep-card">
                        <div class="dep-grid">
                            <div>
                                <h3 style="margin: 0 0 6px 0; color: var(--primary-blue);"><?php echo $dep['full_name']; ?></h3>
                                <p class="dep-meta"><span class="dep-label">Relationship:</span> <?php echo $dep['relationship']; ?></p>
                                <p class="dep-meta"><span class="dep-label">Date of Birth:</span> <?php echo $dep['dob']; ?></p>
                                <p class="dep-meta"><span class="dep-label">Gender:</span> <?php echo $dep['gender']; ?></p>
                                <p class="dep-meta"><span class="dep-label">Blood Group:</span> <?php echo $dep['blood_group'] ?? 'Unknown'; ?></p>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 8px 0; color: var(--primary-blue);">Recent Visits</h4>
                                <?php
                                    $history = dep_history($conn, $dep['dep_id'], $history_table_exists);
                                ?>
                                <?php if (count($history) > 0): ?>
                                    <?php foreach ($history as $rec): ?>
                                        <div class="history-card">
                                            <div class="history-row">
                                                <strong style="color: var(--primary-blue);"><?php echo $rec['facility_name'] ?? 'Facility'; ?></strong>
                                                <small style="color: #7a8b94;"><?php echo date('M d, Y', strtotime($rec['created_at'])); ?></small>
                                            </div>
                                            <p style="margin: 6px 0 0 0; color: #4b5563;"><strong>Diagnosis:</strong> <?php echo $rec['diagnosis'] ?? 'N/A'; ?></p>
                                            <p style="margin: 6px 0 0 0; color: #4b5563;"><strong>Treatment:</strong> <?php echo $rec['treatment'] ?? 'N/A'; ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="empty-note">No visits recorded for this dependent.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="dep-card">
                    <p class="empty-note">No dependents linked to this account yet.</p>
                </div>
            <?php endif; ?>

            <?php if (!$history_table_exists): ?>
                <div style="margin-top: 20px; color: #9aa3a6; font-size: 0.85rem;">
                    Note: Dependent medical history will appear here once visits are recorded for dependents.
                </div>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>


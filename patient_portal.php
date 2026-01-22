<?php
session_start();
require_once 'config/db.php';

// 1. SECURITY: Only allow patients
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: login.php?error=Access Denied");
    exit();
}

$national_id = $_SESSION['username']; // Assuming they log in with their ID

// 2. Fetch Patient's Personal Info
$patient_q = mysqli_query($conn, "SELECT * FROM patients WHERE national_id = '$national_id'");
$patient = mysqli_fetch_assoc($patient_q);

// 3. Fetch All Medical Records for this Citizen
$history_sql = "SELECT * FROM national_health_records WHERE national_id = '$national_id' ORDER BY created_at DESC";
$history_res = mysqli_query($conn, $history_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Afya Poa | Citizen Health Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; }
        .badge-verified { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .timeline-card { transition: transform 0.2s ease, box-shadow 0.2s ease; border: 1px solid #e2e8f0; }
        .timeline-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .sidebar-link { display: flex; align-items: center; gap: 10px; padding: 12px 15px; border-radius: 8px; transition: 0.3s; color: #cbd5e1; text-decoration: none; margin-bottom: 5px; }
        .sidebar-link:hover, .sidebar-link.active { background: #475569; color: white; }
    </style>
</head>
<body class="dashboard-page">

    <nav class="navbar" style="background: #0f172a; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; color: white;">
        <a href="patient_portal.php" class="nav-logo" style="font-size: 1.5rem; font-weight: 700; color: white; text-decoration: none;">Afya <span style="color: #38bdf8;">Poa</span></a>
        <div style="display: flex; gap: 20px; align-items: center;">
            <span style="font-size: 0.9rem; color: #94a3b8;">Welcome back, <?php echo explode(' ', $patient['full_name'])[0]; ?></span>
            <a href="logout.php" style="background: #ef4444; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Logout</a>
        </div>
    </nav>

    <div class="dashboard-wrapper" style="display: flex; min-height: calc(100vh - 70px);">
        <aside class="sidebar" style="width: 260px; background: #1e293b; padding: 25px; display: flex; flex-direction: column;">
            <h4 style="color: #64748b; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; margin-bottom: 20px;">Main Menu</h4>
            <a href="patient_portal.php" class="sidebar-link active"><span>📅</span> Health Timeline</a>
            <a href="#" class="sidebar-link"><span>👨‍👩‍👧‍👦</span> Dependents</a>
            <a href="#" class="sidebar-link"><span>💳</span> Health Card</a>
            <a href="#" class="sidebar-link"><span>⚙️</span> Settings</a>
            
            <div style="margin-top: auto; padding: 15px; background: #334155; border-radius: 12px; border: 1px solid #475569;">
                <p style="margin: 0; color: #94a3b8; font-size: 0.75rem;">Identity Verified</p>
                <p style="margin: 4px 0 0 0; color: white; font-weight: 600; font-size: 0.9rem;"><?php echo $patient['full_name']; ?></p>
            </div>
        </aside>

        <main style="flex: 1; padding: 40px; overflow-y: auto;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
                <div>
                    <h1 style="margin: 0; color: #1e293b; font-size: 1.8rem;">Personal Health Records</h1>
                    <p style="color: #64748b; margin-top: 5px;">National Identifier: <strong><?php echo $national_id; ?></strong></p>
                </div>
                <div class="status-badge badge-verified">✓ Registry Verified</div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px;">
                <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 4px solid #3b82f6;">
                    <p style="margin: 0; color: #64748b; font-size: 0.8rem;">Blood Group</p>
                    <h2 style="margin: 5px 0 0 0; color: #1e293b;"><?php echo $patient['blood_group'] ?? 'N/A'; ?></h2>
                </div>
                <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 4px solid #10b981;">
                    <p style="margin: 0; color: #64748b; font-size: 0.8rem;">Total Visits</p>
                    <h2 style="margin: 5px 0 0 0; color: #1e293b;"><?php echo mysqli_num_rows($history_res); ?></h2>
                </div>
                <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-top: 4px solid #f59e0b;">
                    <p style="margin: 0; color: #64748b; font-size: 0.8rem;">Last Check-up</p>
                    <h2 style="margin: 5px 0 0 0; color: #1e293b;">Recent</h2>
                </div>
            </div>

            <div style="background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                <h3 style="color: #1e293b; margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                    <span style="background: #3b82f6; color: white; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 0.9rem;">📋</span> 
                    Medical Timeline
                </h3>
                
                <?php if(mysqli_num_rows($history_res) > 0): ?>
                    <div style="position: relative; padding-left: 20px; border-left: 2px solid #e2e8f0;">
                        <?php while($record = mysqli_fetch_assoc($history_res)): ?>
                            <div class="timeline-card" style="background: white; padding: 25px; border-radius: 12px; margin-bottom: 25px; position: relative;">
                                <div style="position: absolute; left: -31px; top: 30px; width: 20px; height: 20px; background: #3b82f6; border: 4px solid white; border-radius: 50%;"></div>
                                
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                                    <div>
                                        <span style="font-size: 0.8rem; font-weight: 700; color: #3b82f6; background: #eff6ff; padding: 4px 10px; border-radius: 6px;">
                                            <?php echo date('F d, Y', strtotime($record['created_at'])); ?>
                                        </span>
                                        <h4 style="margin: 15px 0 5px 0; font-size: 1.2rem; color: #ef4444;">Diagnosis: <?php echo $record['diagnosis']; ?></h4>
                                    </div>
                                    <div style="text-align: right;">
                                        <p style="margin: 0; font-size: 0.85rem; color: #64748b;">Facility</p>
                                        <p style="margin: 0; font-weight: 600; color: #1e293b;"><?php echo $record['facility_name'] ?? 'National Hospital'; ?></p>
                                    </div>
                                </div>

                                <div style="background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                    <p style="margin: 0; color: #475569; line-height: 1.6;"><strong>Prescribed Treatment:</strong><br> <?php echo $record['treatment']; ?></p>
                                </div>

                                <div style="display: flex; align-items: center; gap: 10px; color: #64748b; font-size: 0.85rem;">
                                    <div style="width: 24px; height: 24px; background: #cbd5e1; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">D</div>
                                    Attending Practitioner: <span style="color: #1e293b; font-weight: 600;">Dr. <?php echo $record['doctor_name']; ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px;">
                        <img src="https://cdn-icons-png.flaticon.com/512/6598/6598519.png" style="width: 80px; opacity: 0.2; margin-bottom: 20px;">
                        <p style="color: #94a3b8; font-size: 1.1rem;">Your National Digital Health Record is currently empty.</p>
                        <p style="color: #cbd5e1; font-size: 0.9rem;">Records will appear here after your first facility visit.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>
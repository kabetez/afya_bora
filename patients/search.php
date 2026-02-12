<?php
session_start();
require_once '../config/db.php';

// Security: Only authorized medical personnel (doctors)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: ../login.php");
    exit();
}

$results = null;
$search_term = "";

if (isset($_POST['search_btn'])) {
    $search_term = mysqli_real_escape_string($conn, $_POST['search_term']);
    
    // Logic: Search the National Database by ID or Name
    $sql = "SELECT * FROM patients 
            WHERE national_id = '$search_term' 
            OR full_name LIKE '%$search_term%' 
            ORDER BY full_name ASC";
            
    $results = mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>National Health Registry | Search</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">

    <nav class="navbar">
        <div class="nav-container">
            <span class="nav-logo">Afya Bora | National Health System</span>
            <ul class="nav-links">
                <li><a href="../doctor_dashboard.php">Dashboard</a></li>
                <li><a href="add.php">New Citizen Entry</a></li>
                <li><a href="../logout.php" class="logout-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <h4 style="color: #fff; border-bottom: 1px solid #ffffff55; padding-top: 10px;padding-bottom: 10px;">Registry Tools</h4>
            <p style="font-size: 0.8rem; color: #ddd;">Searching by National ID is recommended for accuracy.</p>
        </aside>

        <main class="container">
            <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 25px; border-left: 10px solid var(--primary-blue); box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                <h2 style="margin:0; color: var(--primary-blue);">Central Patient Registry</h2>
                <p style="color: #666;">Query the national database for medical history and identity verification.</p>
                
                <form method="POST" action="search.php" style="display: flex; gap: 10px; margin-top: 20px;">
                    <input type="text" name="search_term" placeholder="Enter National ID or Full Name..." 
                           value="<?php echo htmlspecialchars($search_term); ?>" 
                           style="flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 5px;" required>
                    <button type="submit" name="search_btn" style="width: 200px; background-color: var(--accent-maroon);">Search Registry</button>
                </form>
            </div>

            <?php if ($results): ?>
                <div class="dashboard-card" style="width: 100%; box-sizing: border-box;">
                    <h3 style="color: var(--primary-blue); margin-top: 0;">Search Results</h3>
                    
                    <?php if (mysqli_num_rows($results) > 0): ?>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                            <thead>
                                <tr style="background-color: #f8f9fa; border-bottom: 2px solid var(--primary-blue);">
                                    <th style="padding: 12px; text-align: left;">Full Name</th>
                                    <th style="padding: 12px; text-align: left;">National ID</th>
                                    <th style="padding: 12px; text-align: left;">County of Origin</th>
                                    <th style="padding: 12px; text-align: center;">Record</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($results)): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 12px;"><strong><?php echo $row['full_name']; ?></strong></td>
                                        <td style="padding: 12px; font-family: monospace;"><?php echo $row['national_id']; ?></td>
                                        <td style="padding: 12px;"><?php echo isset($row['county']) ? $row['county'] : 'Not Set'; ?></td>
                                        <td style="padding: 12px; text-align: center;">
                                            <a href="view.php?id=<?php echo $row['patient_id']; ?>" 
                                               style="color: var(--accent-maroon); text-decoration: none; font-weight: bold;">
                                               Open File ➔
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: #d9534f;">
                            <strong>No records found.</strong> If this citizen is not in the system, please <a href="add.php">register them here</a>.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

</body>
</html>

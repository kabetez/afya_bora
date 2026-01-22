<?php
session_start();
require_once '../config/db.php';

// Security check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'patient') {
    header("Location: ../login.php");
    exit();
}

// Search Logic
$search_specialty = $_GET['specialty'] ?? '';
$query = "SELECT id, username, specialty, hospital_name FROM users WHERE role='doctor'";

if (!empty($search_specialty)) {
    $query .= " AND specialty = '$search_specialty'";
}

$doctors_res = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Afya Hub | Find a Provider</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-wrapper">
        <main style="flex: 1; padding: 30px; background: #f8f9fa;">
            <h2>Find a Specialist</h2>
            <p>Search the National Registry for available medical practitioners.</p>

            <form method="GET" style="margin-bottom: 30px; display: flex; gap: 10px;">
                <select name="specialty" style="padding: 10px; border-radius: 5px; flex: 1;">
                    <option value="">-- All Specialties --</option>
                    <option value="General Practitioner">General Practitioner</option>
                    <option value="Pediatrics">Pediatrics</option>
                    <option value="Cardiology">Cardiology</option>
                    <option value="Surgery">Surgery</option>
                </select>
                <button type="submit" style="background: #1a3a5a; color: white; padding: 10px 25px; border: none; border-radius: 5px; cursor: pointer;">Search</button>
            </form>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                <?php while($doc = mysqli_fetch_assoc($doctors_res)): ?>
                <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid #1a3a5a;">
                    <h3 style="margin: 0;">Dr. <?php echo $doc['username']; ?></h3>
                    <p style="color: #e74c3c; font-weight: bold; margin: 5px 0;"><?php echo $doc['specialty']; ?></p>
                    <p style="color: #666; font-size: 0.9rem;">📍 <?php echo $doc['hospital_name']; ?></p>
                    <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
                    <a href="book_appointment.php?doc_id=<?php echo $doc['id']; ?>" 
                       style="display: block; text-align: center; background: #1a3a5a; color: white; text-decoration: none; padding: 10px; border-radius: 5px;">
                       Schedule Visit
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
        </main>
    </div>
</body>
</html>
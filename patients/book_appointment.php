<?php
session_start();
require_once '../config/db.php';

$doc_id = $_GET['doc_id'];
$patient_id = $_SESSION['user_id'];

if (isset($_POST['book_btn'])) {
    $date = $_POST['app_date'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    $sql = "INSERT INTO appointments (patient_id, doctor_id, app_date, reason, status) 
            VALUES ('$patient_id', '$doc_id', '$date', '$reason', 'Pending')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: ../patient_portal.php?msg=Booking Request Sent");
        exit();
    }
}
?>

<div class="dashboard-card" style="max-width: 500px; margin: 50px auto; padding: 30px;">
    <h3>Request Appointment</h3>
    <form method="POST">
        <label>Select Date:</label>
        <input type="date" name="app_date" required style="width: 100%; padding: 10px; margin-bottom: 20px;">
        
        <label>Reason for Visit:</label>
        <textarea name="reason" placeholder="Briefly describe your symptoms..." style="width: 100%; padding: 10px; height: 100px;"></textarea>
        
        <button name="book_btn" style="width: 100%; background: #27ae60; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer;">
            Confirm Request
        </button>
    </form>
</div>
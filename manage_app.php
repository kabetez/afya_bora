<?php
session_start();
require_once 'config/db.php';

// 1. SECURITY: Only logged-in doctors can manage appointments
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'doctor') {
    header("Location: login.php");
    exit();
}

// 2. GET DATA: We need the Appointment ID and the New Status from the URL
if (isset($_GET['id']) && isset($_GET['status'])) {
    
    $app_id = mysqli_real_escape_string($conn, $_GET['id']);
    $new_status = mysqli_real_escape_string($conn, $_GET['status']);
    $doctor_id = $_SESSION['user_id'];

    // 3. UPDATE DATABASE: We ensure the doctor can only update THEIR own appointments
    $sql = "UPDATE appointments 
            SET status = '$new_status' 
            WHERE id = '$app_id' AND doctor_id = '$doctor_id'";

    if (mysqli_query($conn, $sql)) {
        // Redirect back with a success message
        header("Location: doctor_dashboard.php?msg=Appointment $new_status Successfully");
        exit();
    } else {
        // If it fails, show the error
        die("Error updating appointment: " . mysqli_error($conn));
    }
} else {
    // If someone tries to access this page directly without IDs
    header("Location: doctor_dashboard.php");
    exit();
}
?>
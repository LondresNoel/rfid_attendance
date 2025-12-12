<?php
include 'db.php';

if (isset($_GET['date'])) {
    $date = $_GET['date'];

    // Delete attendance records for that date
    $stmt1 = $conn->prepare("DELETE FROM attendance_records WHERE date = ?");
    $stmt1->bind_param("s", $date);
    $stmt1->execute();

    // Delete date from attendance_dates
    $stmt2 = $conn->prepare("DELETE FROM attendance_dates WHERE date = ?");
    $stmt2->bind_param("s", $date);
    $stmt2->execute();

    header("Location: index.php?deleted=1");
    exit();
}
?>

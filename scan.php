<?php
session_start();
include "db.php";

if (!isset($_POST['rfid'])) {
    http_response_code(400);
    die("No RFID received");
}

$rfid = $conn->real_escape_string($_POST['rfid']);
$active_date = isset($_POST['date']) ? $conn->real_escape_string($_POST['date']) : date("Y-m-d");

$time = date("H:i:s");

// Find student
$res = $conn->query("SELECT * FROM students WHERE rfid='$rfid' LIMIT 1");
if ($res->num_rows == 0) {
    http_response_code(404);
    die("Unknown RFID");
}

$student = $res->fetch_assoc();
$student_id = $student['id'];
$name = $student['name'];

// Already logged?
$check = $conn->query("SELECT * FROM attendance WHERE student_id=$student_id AND date='$active_date' LIMIT 1");
if ($check->num_rows > 0) {
    echo "Already logged for $name on $active_date";
    exit;
}

// Late check
$late = (strtotime($time) > strtotime("07:01:00")) ? 1 : 0;

// Insert attendance
$stmt = $conn->prepare("INSERT INTO attendance (student_id, date, time_in, status, late) VALUES (?, ?, ?, 'present', ?)");
$stmt->bind_param("issi", $student_id, $active_date, $time, $late);
$stmt->execute();

echo "Attendance logged for $name on $active_date";
?>

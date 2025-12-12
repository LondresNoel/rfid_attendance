<?php
session_start();
include "db.php";

if (!isset($_GET['tag'])) {
    http_response_code(400);
    die("No RFID Tag received");
}

// Normalize tag (remove spaces / lowercase -> DB stored as entered)
$rfid_raw = $conn->real_escape_string($_GET['tag']);
$rfid = str_replace(' ', '', $rfid_raw);

// active date: from GET if provided, else session, else today
$active_date = isset($_GET['date']) ? $conn->real_escape_string($_GET['date'])
             : (isset($_SESSION['active_date']) ? $_SESSION['active_date'] : date("Y-m-d"));

$now_time = date("H:i:s");

// Find student; ignore spaces in DB too
$res = $conn->query("SELECT * FROM students WHERE REPLACE(rfid,' ','') = '$rfid' LIMIT 1");
if ($res->num_rows == 0) {
    http_response_code(404);
    die("Unknown RFID");
}
$student = $res->fetch_assoc();
$student_id = (int)$student['id'];

// Ensure attendance row exists for this date
$check = $conn->query("SELECT * FROM attendance WHERE student_id=$student_id AND date='$active_date' LIMIT 1");
if ($check->num_rows == 0) {
    $conn->query("INSERT INTO attendance (student_id, date, status, late) VALUES ($student_id, '$active_date', 'absent', 0)");
}


// If already present, stop
$already = $conn->query("SELECT * FROM attendance WHERE student_id=$student_id AND date='$active_date' AND status='present' LIMIT 1");
if ($already->num_rows > 0) {
    echo "Already logged for {$student['name']} on $active_date";
    exit;
}

// Determine class schedule for this student
$class_start = $student['class_start']; // TIME or NULL
$class_end   = $student['class_end'];   // TIME or NULL

// Default behaviour if schedule missing: allow present (not strict)
if (empty($class_start) || empty($class_end)) {
    // treat as present
    $is_present = true;
    $is_late = 0;
} else {
    // Compare times
    // late threshold = class_start + 5 minutes
    $late_threshold = date("H:i:s", strtotime($class_start) + 5*60);

    if ($now_time >= $class_start && $now_time <= $class_end) {
        // within class window -> present
        $is_present = true;
        $is_late = ($now_time > $late_threshold) ? 1 : 0;
    } else {
        // outside class window -> treat as absent
        $is_present = false;
        $is_late = 0;
    }
}

// Update attendance row accordingly
if ($is_present) {
    $stmt = $conn->prepare("UPDATE attendance SET status='present', time_in=?, late=? WHERE student_id=? AND date=?");
    $stmt->bind_param("siis", $now_time, $is_late, $student_id, $active_date);
    $stmt->execute();
    echo "Present recorded for {$student['name']} on $active_date (late={$is_late})";
} else {
    // ensure row has absent (it's already created above). keep late = 0
    $stmt = $conn->prepare("UPDATE attendance SET status='absent', late=0 WHERE student_id=? AND date=?");
    $stmt->bind_param("is", $student_id, $active_date);
    $stmt->execute();
    echo "Scan outside class hours — {$student['name']} remains absent on $active_date";
}
?>

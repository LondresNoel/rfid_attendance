<?php
session_start();
include "db.php";

if (!isset($_GET['tag'])) {
    http_response_code(400);
    die("No RFID Tag received");
}

$rfid_raw = $conn->real_escape_string($_GET['tag']);
$rfid = str_replace(' ', '', $rfid_raw);

// Active date (default to today)
$active_date = isset($_GET['date'])
    ? $conn->real_escape_string($_GET['date'])
    : date("Y-m-d");

$now_time = date("H:i:s");

// ================================
// FIND STUDENT
// ================================
$res = $conn->query("
    SELECT id, name, block 
    FROM students 
    WHERE REPLACE(rfid,' ','') = '$rfid'
    LIMIT 1
");

if ($res->num_rows == 0) {
    die("Unknown RFID");
}

$student = $res->fetch_assoc();
$student_id = (int)$student['id'];
$block_id   = (int)$student['block'];

// ================================
// GET BLOCK TIME
// ================================
$blockQ = $conn->query("
    SELECT start_time, end_time 
    FROM blocks 
    WHERE id = $block_id 
    LIMIT 1
");

if ($blockQ->num_rows == 0) {
    die("Block time not configured");
}

$block = $blockQ->fetch_assoc();
$start_time = $block['start_time'];
$end_time   = $block['end_time'];

// ================================
// ENSURE ATTENDANCE ROW EXISTS
// ================================
$check = $conn->query("
    SELECT id FROM attendance 
    WHERE student_id=$student_id 
    AND date='$active_date'
    LIMIT 1
");

if ($check->num_rows == 0) {
    $conn->query("
        INSERT INTO attendance (student_id, date, status, late)
        VALUES ($student_id, '$active_date', 'absent', 0)
    ");
}

// ================================
// PREVENT DOUBLE SCAN
// ================================
$already = $conn->query("
    SELECT status FROM attendance
    WHERE student_id=$student_id
    AND date='$active_date'
    AND status='present'
    LIMIT 1
");

if ($already->num_rows > 0) {
    echo "Already recorded for {$student['name']}";
    exit;
}

// ================================
// TIME COMPUTATIONS
// ================================
$start_ts = strtotime($start_time);
$now_ts   = strtotime($now_time);
$end_ts   = strtotime($end_time);

$on_time_limit = $start_ts + (5 * 60);    // 5 minutes after start
$late_limit    = $start_ts + (30 * 60);   // 30 minutes after start

// ================================
// ATTENDANCE DECISION
// ================================
if ($now_ts < $start_ts) {
    echo "Too early — ABSENT";
    exit;
}

if ($now_ts > $end_ts) {
    echo "Class ended — ABSENT";
    exit;
}

// Prepare statement once
$stmt = $conn->prepare("
    UPDATE attendance 
    SET status='present', time_in=?, late=?
    WHERE student_id=? AND date=?
");

if ($now_ts <= $on_time_limit) {
    $late_flag = 0;
    echo "Present (ON TIME) — {$student['name']}";
} elseif ($now_ts <= $late_limit) {
    $late_flag = 1;
    echo "Present (LATE) — {$student['name']}";
} else {
    echo "More than 30 minutes late — ABSENT";
    exit;
}

// Execute attendance update
$stmt->bind_param("siis", $now_time, $late_flag, $student_id, $active_date);
$stmt->execute();

// ================================
// AUTO-CREATE TODAY'S DATE FOR BLOCK
// ================================
$checkDate = $conn->query("
    SELECT 1 FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE a.date = '$active_date'
    AND s.block = $block_id
    LIMIT 1
");

if ($checkDate->num_rows == 0) {
    $studentsQ = $conn->query("SELECT id FROM students WHERE block = $block_id");

    while ($st = $studentsQ->fetch_assoc()) {
        $sid = (int)$st['id'];
        $conn->query("
            INSERT INTO attendance (student_id, date, status, late)
            VALUES ($sid, '$active_date', 'absent', 0)
        ");
    }
}
?>

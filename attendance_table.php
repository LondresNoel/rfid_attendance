<?php
include "db.php";

// Get date
$active_date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");
$block_id = isset($_GET['block']) ? intval($_GET['block']) : 1;

// Get all attendance dates for the block
$dates_res = $conn->query("
    SELECT DISTINCT a.date 
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE s.block = $block_id
    ORDER BY a.date ASC
");

$date_arr = [];
while ($r = $dates_res->fetch_assoc()) $date_arr[] = $r['date'];

// Get students in this block
$students = $conn->query("
    SELECT * FROM students 
    WHERE block = $block_id
    ORDER BY name ASC
");

// Build table
echo "<table class='table' style='border-collapse:collapse;width:100%;'>";
echo "<tr>
        <th>Name</th>";
foreach ($date_arr as $d) {
    // Highlight active date column header
    $header_style = "";
    echo "<th style='$header_style'>$d</th>";
}
echo "<th>Late</th><th>Absent</th><th>Status</th></tr>";

// Student rows
while ($s = $students->fetch_assoc()) {

    // Count total absences
    $count_abs = $conn->query("
        SELECT COUNT(*) AS missing 
        FROM attendance 
        WHERE student_id={$s['id']} 
        AND status='absent'
    ")->fetch_assoc()['missing'];

    // Determine row color and status
    if ($count_abs >= 5) {
        $status = "DROPPED";
        $row_color = "background-color:#ffb3b3;font-weight:bold;"; // red
    } elseif ($count_abs >= 3) {
        $status = "ACTIVE";
        $row_color = "background-color:#fff5b1;"; // yellow warning
    } else {
        $status = "ACTIVE";
        $row_color = ""; // default
    }

    echo "<tr style='$row_color'>";
    echo "<td>{$s['name']}</td>";

    // Attendance per date
    foreach ($date_arr as $d) {
        $a = $conn->query("
            SELECT status 
            FROM attendance 
            WHERE student_id={$s['id']} AND date='$d' LIMIT 1
        ");
        $cell_style = ""; // active date highlight
        if ($a->num_rows > 0) {
            $ar = $a->fetch_assoc();
            if ($ar['status'] === 'present') {
                echo "<td style='background:#d4edda;font-weight:bold;$cell_style'>√</td>"; // green
            } elseif ($ar['status'] === 'absent') {
                echo "<td style='background:#f8d7da;font-weight:bold;$cell_style'>--</td>"; // light red
            } else {
                echo "<td style='$cell_style'>-</td>"; // placeholder
            }
        } else {
            echo "<td style='$cell_style'>-</td>";
        }
    }

    // Total late for the student in this block
$late_row = $conn->query("
    SELECT SUM(late) AS total_late 
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE s.block = $block_id AND a.student_id={$s['id']}
");
$late = ($late_row->num_rows > 0) ? intval($late_row->fetch_assoc()['total_late']) : 0;

// Total absent for the student in this block
$abs_row = $conn->query("
    SELECT COUNT(*) AS total_absent
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE s.block = $block_id AND a.student_id={$s['id']} AND a.status='absent'
");
$absent = ($abs_row->num_rows > 0) ? intval($abs_row->fetch_assoc()['total_absent']) : 0;


    echo "<td>$late</td>";
    echo "<td>$absent</td>";
    echo "<td>$status</td>";

    echo "</tr>";
}

echo "</table>";
?>

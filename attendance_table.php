<?php
include "db.php";

// Get date
$active_date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");

// Get block number coming from BLOCK_X.php
$block_id = isset($_GET['block']) ? intval($_GET['block']) : 1;

// ===============================================
// GET ALL DATES THAT HAVE ATTENDANCE FOR THIS BLOCK
// ===============================================
$dates_res = $conn->query("
    SELECT DISTINCT a.date 
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE s.block = $block_id
    ORDER BY a.date ASC
");

$date_arr = [];
while ($r = $dates_res->fetch_assoc()) {
    $date_arr[] = $r['date'];
}

// ===============================================
// GET STUDENTS FOR THIS BLOCK ONLY
// ===============================================
$students = $conn->query("
    SELECT * FROM students 
    WHERE block = $block_id
    ORDER BY name ASC
");

// ===============================================
// BUILD TABLE
// ===============================================
echo "<table class='table' style='border-collapse:collapse; width:100%;'>";
echo "<tr>
        <th>Name</th>";
foreach ($date_arr as $d) echo "<th>$d</th>";
echo "<th>Late</th><th>Absent</th><th>Status</th></tr>";

// Student rows
while ($s = $students->fetch_assoc()) {

    // Count total absences (all time)
    $count_abs = $conn->query("
        SELECT COUNT(*) AS missing 
        FROM attendance 
        WHERE student_id={$s['id']} 
        AND status='absent'
    ")->fetch_assoc()['missing'];

    // Determine row color
    if ($count_abs >= 5) {
        $conn->query("UPDATE students SET status='dropped' WHERE id={$s['id']}");
        $status = "DROPPED";
        $row_color = "style='background:#ffb3b3;font-weight:bold;'";
    } elseif ($count_abs >= 3) {
        $status = "ACTIVE";
        $row_color = "style='background:#fff5b1;'"; // yellow warning
    } else {
        $status = "ACTIVE";
        $row_color = "";
    }

    echo "<tr $row_color>";
    echo "<td>{$s['name']}</td>";

    // Show √ or blank per date, green background if present
    foreach ($date_arr as $d) {
        $a = $conn->query("
            SELECT status 
            FROM attendance 
            WHERE student_id={$s['id']} 
            AND date='$d'
            LIMIT 1
        ");

        if ($a->num_rows > 0) {
            $ar = $a->fetch_assoc();
            if ($ar['status'] === 'present') {
                echo "<td style='background:#d4edda;font-weight:bold;'>√</td>"; // green box
            } else {
                echo "<td>-</td>";
            }
        } else {
            echo "<td>-</td>";
        }
    }

    // LATE depends on selected date
    $row = $conn->query("
        SELECT * FROM attendance 
        WHERE student_id={$s['id']} 
        AND date='$active_date' 
        LIMIT 1
    ");
    $late = 0;
    if ($row->num_rows > 0) {
        $r = $row->fetch_assoc();
        $late = intval($r['late']);
    }

    echo "<td class='late'>$late</td>";
    echo "<td class='absent'>$count_abs</td>";
    echo "<td>$status</td>";

    echo "</tr>";
}

echo "</table>";
?>


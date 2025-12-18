<?php
session_start();
include "db.php";

$block_id = isset($_GET['block']) ? (int)$_GET['block'] : 1;

// Get block info
$block = mysqli_fetch_assoc(
    $conn->query("SELECT * FROM blocks WHERE id=$block_id")
);

// Get all students in this block
$students_res = $conn->query("SELECT id, name FROM students WHERE block=$block_id ORDER BY name ASC");

// Get all attendance for this block
$attendance_res = $conn->query("
    SELECT a.student_id, a.date, a.status, a.late
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE s.block=$block_id
    ORDER BY a.student_id, a.date
");

// Organize attendance by student
$attendance = [];
while ($row = $attendance_res->fetch_assoc()) {
    $attendance[$row['student_id']][$row['date']] = $row;
}

// Get all dates
$dates_res = $conn->query("
    SELECT DISTINCT a.date
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE s.block = $block_id
    ORDER BY a.date ASC
");

$dates = [];
while ($d = $dates_res->fetch_assoc()) $dates[] = $d['date'];
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Block Summary - <?php echo $block['block_name']; ?></title>
<style>
body { font-family:'Segoe UI'; background:#f5f7fa; padding:20px; }
h1 { text-align:center; }
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:8px; text-align:center; }
th { background:#2980b9; color:white; }
.present { background:#2ecc71; color:white; }
.absent { background:#e74c3c; color:white; }
.late { background:#f1c40f; color:white; }
#back { display:inline-block; margin-top:20px; background:#2c3e50; color:white; padding:10px 15px; border-radius:5px; text-decoration:none; }
</style>
</head>
<body>

<h1>Attendance Summary - <?php echo $block['block_name']; ?></h1>

<table>
    <tr>
        <th>Student</th>
        <?php foreach($dates as $d): ?>
            <th><?php echo $d; ?></th>
        <?php endforeach; ?>
    </tr>

    <?php while ($student = $students_res->fetch_assoc()): ?>
        <tr>
            <td><?php echo $student['name']; ?></td>
            <?php foreach($dates as $d): 
                $att = $attendance[$student['id']][$d] ?? null;
                if ($att) {
                    $status = $att['status'];
                    $late = $att['late'];
                    if ($status == 'absent') $class = 'absent';
                    elseif ($late > 0) $class = 'late';
                    else $class = 'present';
                } else {
                    $status = '-';
                    $class = '';
                }
            ?>
                <td class="<?php echo $class; ?>">
                    <?php 
                        if ($status == 'present') echo 'P';
                        elseif ($status == 'absent') echo 'A';
                        elseif ($class == 'late') echo 'L';
                        else echo '-';
                    ?>
                </td>
            <?php endforeach; ?>
        </tr>
    <?php endwhile; ?>
</table>

 <?php
// Get previous page URL
$back_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
?>


<a href="<?php echo $back_url; ?>" id="back" style="
    display:inline-block; 
    margin-top:20px; 
    background:#2c3e50; 
    color:white; 
    padding:10px 15px; 
    border-radius:5px; 
    text-decoration:none;">
    ← Back
</a>


</body>
</html>

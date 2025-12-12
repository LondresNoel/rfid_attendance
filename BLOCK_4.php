<?php
session_start();
include "db.php";
$block_id = 4;

// NEW — per-block session key
$session_key = "active_date_block_" . $block_id;

// ====================================================
// DELETE DATE (NEW FEATURE)
// ====================================================
if (isset($_POST['delete_date'])) {
    $del_date = $conn->real_escape_string($_POST['delete_date']);

    // Delete ONLY attendance records for this block on this date
    $conn->query("
        DELETE a FROM attendance a
        JOIN students s ON a.student_id = s.id
        WHERE a.date='$del_date' AND s.block=$block_id
    ");

    // Remove active date session if deleted
    if (isset($_SESSION[$session_key]) && $_SESSION[$session_key] == $del_date) {
        unset($_SESSION[$session_key]);
    }

    $message = "<div style='color:red;'>Date $del_date deleted for Block $block_id.</div>";
}


// ADD NEW DATE (Block-specific) — improved: past -> absent, today -> absent, future -> blank
if (isset($_POST['new_date'])) {
    $new_date = $conn->real_escape_string($_POST['new_date']);
    $today = date("Y-m-d");

    // Check if this block already has attendance rows for that date
    $check = $conn->query("
        SELECT 1 FROM attendance a
        JOIN students s ON a.student_id = s.id
        WHERE a.date='$new_date' AND s.block=$block_id LIMIT 1
    ");

    if ($check->num_rows == 0) {
        // For this block only
        $studentsQ = $conn->query("SELECT id FROM students WHERE block = $block_id");
        while ($s = $studentsQ->fetch_assoc()) {
            $sid = (int)$s['id'];

            if ($new_date < $today) {
                // Past date -> auto mark absent
                $conn->query("
                    INSERT INTO attendance (student_id, date, status, late, time_in)
                    VALUES ($sid, '$new_date', 'absent', 0, NULL)
                ");
            } elseif ($new_date == $today) {
                // Today -> mark absent until scanned (existing behavior)
                $conn->query("
                    INSERT INTO attendance (student_id, date, status, late, time_in)
                    VALUES ($sid, '$new_date', 'absent', 0, NULL)
                ");
            } else {
                // Future date -> placeholder rows, no absent yet
                $conn->query("
                    INSERT INTO attendance (student_id, date, status, late, time_in)
                    VALUES ($sid, '$new_date', NULL, 0, NULL)
                ");
            }
        }

        $_SESSION[$session_key] = $new_date;
        $message = "<div style='color:green'>Date $new_date added for Block $block_id!</div>";

    } else {
        $_SESSION[$session_key] = $new_date;
        $message = "<div style='color:orange'>Date already exists for this block!</div>";
    }
}


// SWITCH ACTIVE DATE
if (isset($_GET['date'])) {
    $_SESSION[$session_key] = $_GET['date'];
}

$active_date = $_SESSION[$session_key] ?? date("Y-m-d");

// GET ALL DATES
$dates_res = $conn->query("
    SELECT DISTINCT a.date
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE s.block = $block_id
    ORDER BY a.date ASC
");

$date_arr = [];
while ($d = $dates_res->fetch_assoc()) $date_arr[] = $d['date'];
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Attendance Dashboard</title>
<style>
body { font-family: 'Segoe UI'; background:#f5f7fa; }
h1 { text-align:center; color:#2c3e50; margin:20px 0; }
.date-selector { text-align:center; margin-bottom:20px; }
.date-selector a { margin:0 5px; text-decoration:none; color:#2980b9; }
.date-selector a.active { font-weight:bold; text-decoration:underline; }
table { width:100%; border-collapse:collapse; }
th, td { padding:8px; border:1px solid #ccc; text-align:center; }
#back-home-btn {
    position: fixed; bottom: 20px; right: 20px;
    background: #2c3e50; color: white; padding: 12px 18px;
    border-radius: 8px; text-decoration: none; font-weight: bold;
}
#back-home-btn:hover { background:#1a252f; }
.delete-btn {
    background:#e74c3c; color:white; border:none; padding:6px 10px;
    border-radius:5px; cursor:pointer; margin-top:10px;
}
</style>
</head>
<body>

<h1>Attendance Dashboard - Block 4</h1>


<!-- DELETE STUDENT BUTTON -->
<div style="text-align:center; margin-bottom:15px;">
    <form method="post" action="delete_student.php" 
          onsubmit="return confirm('Delete selected student?');"
          style="display:flex; justify-content:center; gap:10px;">

        <select name="student_id" required
                style="padding:8px; border-radius:5px;">
            <option value="">-- Select Student --</option>
            <?php
            $std = $conn->query("SELECT id, name FROM students WHERE block=$block_id ORDER BY name ASC");
            while ($st = $std->fetch_assoc()) {
                echo "<option value='{$st['id']}'>{$st['name']}</option>";
            }
            ?>
        </select>

        <button type="submit" 
            style="background:#e74c3c; border:none; color:white; padding:8px 12px; border-radius:5px;">
            Delete Student
        </button>
    </form>
</div>


<?php if(isset($message)) echo $message; ?>

<div class="add-date-wrapper">
    <form method="post" class="add-date-form">
        <label>Add New Attendance Date:</label>
        <input type="date" name="new_date" required>
        <input type="submit" value="Add Date">
    </form>
</div>

<style>
.add-date-wrapper {
    width: 100%;
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.add-date-form {
    display: flex;
    gap: 10px;
    align-items: center;
}
</style>


<?php $current_page = basename(__FILE__); ?>

<div class="date-selector">
    <strong>Select Active Date:</strong><br><br>

    <?php foreach($date_arr as $d): ?>
        <a href="<?php echo $current_page; ?>?date=<?php echo $d; ?>" 
           class="<?php echo ($d == $active_date) ? 'active' : ''; ?>">
            <?php echo $d; ?>
        </a>
    <?php endforeach; ?>

    <br><br>

    <!-- DELETE DATE BUTTON -->
    <form method="post" onsubmit="return confirm('Delete date <?php echo $active_date; ?> for Block 1?');">
        <input type="hidden" name="delete_date" value="<?php echo $active_date; ?>">
        <button class="delete-btn"> Delete This Date </button>
    </form>

</div>

<div id="attendance-table"></div>

<script>
function loadAttendance() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'attendance_table.php?date=<?php echo $active_date; ?>&block=<?php echo $block_id; ?>', true);
    xhr.onload = () => document.getElementById("attendance-table").innerHTML = xhr.responseText;
    xhr.send();
}
loadAttendance();
setInterval(loadAttendance, 3000);
</script>


<a href="add_student.php?from_block=<?php echo $block_id; ?>" 
   style="
        position: fixed;
        bottom: 80px;
        right: 20px;
        background:#27ae60;
        color:white;
        padding:12px 18px;
        border-radius:8px;
        text-decoration:none;
        font-weight:bold;">
    + Add Student
</a>


<a href="index.php" id="back-home-btn">← Back to Home</a>

</body>
</html>

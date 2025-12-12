<?php
include "db.php"; // your DB connection
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $rfid = $conn->real_escape_string($_POST['rfid']);
    $block = intval($_POST['block']);
    $class_start = !empty($_POST['class_start']) ? $conn->real_escape_string($_POST['class_start']) : NULL;
    $class_end   = !empty($_POST['class_end'])   ? $conn->real_escape_string($_POST['class_end'])   : NULL;

    // Duplicate RFID check
    $check = $conn->query("SELECT * FROM students WHERE rfid='$rfid' LIMIT 1");
    if ($check->num_rows > 0) {
        $message = "<div style='color:red; margin-bottom:10px;'>RFID already assigned!</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO students (name, rfid, block, class_start, class_end) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $name, $rfid, $block, $class_start, $class_end);
        $stmt->execute();
        $student_id = $stmt->insert_id;
        $stmt->close();

        // Add placeholder attendance for existing dates
        $dates_res = $conn->query("SELECT DISTINCT date FROM attendance");
        while ($d = $dates_res->fetch_assoc()) {
            $date_v = $d['date'];
            $conn->query("INSERT INTO attendance (student_id, date, status, late) VALUES ($student_id, '$date_v', 'absent', 0)");
        }

        $message = "<div style='color:green; margin-bottom:10px;'>Student $name added to Block $block.</div>";

        // Redirect back to block page if link contains ?from_block=#
if (isset($_GET['from_block'])) {
    $blk = intval($_GET['from_block']);
    header("Location: BLOCK_{$blk}.php");
    exit();
}

    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Add Student</title>
<style>
/* minimal styling */
body{font-family:Arial;background:#f5f7fa;padding:20px}
form{background:#fff;padding:20px;border-radius:8px;max-width:500px;margin:0 auto;box-shadow:0 2px 8px rgba(0,0,0,0.08)}
label{display:block;margin-top:10px}
input[type=text], select, input[type=time]{width:100%;padding:8px;border-radius:4px;border:1px solid #ccc}
input[type=submit]{margin-top:15px;padding:10px 15px;background:#2980b9;color:#fff;border:none;border-radius:4px;cursor:pointer}
.message{text-align:center;margin-bottom:10px}
a{display:block;text-align:center;margin-top:12px;color:#2980b9}
</style>
</head>
<body>
<h1 style="text-align:center">Add New Student</h1>
<?php if ($message) echo "<div class='message'>$message</div>"; ?>
<form method="post">
    <label>Name:</label>
    <input type="text" name="name" required>

    <label>RFID (UID):</label>
    <input type="text" name="rfid" required placeholder="e.g. 100D5562">

    <label>Block:</label>
    <select name="block" required>
        <option value="1">Block 1</option>
        <option value="2">Block 2</option>
        <option value="3">Block 3</option>
        <option value="2">Block 4</option>
        <option value="2">Block 5</option>
    </select>

    <label>Class Start (HH:MM)</label>
    <input type="time" name="class_start" required>

    <label>Class End (HH:MM)</label>
    <input type="time" name="class_end" required>

    <input type="submit" value="Add Student">
</form>
<a href="index.php">Back to Dashboard</a>
</body>
</html>

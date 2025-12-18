<?php
session_start();
include "db.php";

// Get block ID from URL, default to 1
$block_id = isset($_GET['block']) ? (int)$_GET['block'] : 1;

// Fetch block info
$block = mysqli_fetch_assoc(
    mysqli_query($conn, "
        SELECT block_name, subject, start_time, end_time 
        FROM blocks 
        WHERE id = $block_id
        LIMIT 1
    ")
);

if (!$block) {
    die("Block not found.");
}

$session_key = "active_date_block_" . $block_id;

// =======================
// DELETE DATE
// =======================
if (isset($_POST['delete_date'])) {
    $del_date = $conn->real_escape_string($_POST['delete_date']);
    $block_id_post = isset($_POST['block']) ? (int)$_POST['block'] : $block_id;

    $conn->query("
        DELETE a FROM attendance a
        JOIN students s ON a.student_id = s.id
        WHERE a.date='$del_date' AND s.block=$block_id_post
    ");

    if (isset($_SESSION[$session_key]) && $_SESSION[$session_key] == $del_date) {
        unset($_SESSION[$session_key]);
    }

    $message = "<div class='message delete'>Date $del_date deleted for Block $block_id_post.</div>";
}

// =======================
// ADD NEW DATE
// =======================
if (isset($_POST['new_date'])) {
    $new_date = $conn->real_escape_string($_POST['new_date']);
    $block_id_post = isset($_POST['block']) ? (int)$_POST['block'] : $block_id;
    $today = date("Y-m-d");

    $check = $conn->query("
        SELECT 1 FROM attendance a
        JOIN students s ON a.student_id = s.id
        WHERE a.date='$new_date' AND s.block=$block_id_post LIMIT 1
    ");

    if ($check->num_rows == 0) {
        $studentsQ = $conn->query("SELECT id FROM students WHERE block = $block_id_post");
        while ($s = $studentsQ->fetch_assoc()) {
            $sid = (int)$s['id'];
            $status = ($new_date <= $today) ? 'absent' : NULL;
            $conn->query("
                INSERT INTO attendance (student_id, date, status, late, time_in)
                VALUES ($sid, '$new_date', '$status', 0, NULL)
            ");
        }
        $message = "<div class='message add'>Date $new_date added for Block $block_id_post!</div>";
    } else {
        $message = "<div class='message exists'>Date already exists for this block!</div>";
    }
}

// =======================
// SET ACTIVE DATE
// =======================
if (isset($_GET['date'])) {
    $_SESSION[$session_key] = $_GET['date'];
}

$active_date = $_SESSION[$session_key] ?? date("Y-m-d");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Attendance Dashboard</title>
<style>
/* ===== Body & Headings ===== */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #ffe29f, #ffa99f, #ff719a, #a18cd1);
    margin: 0;
    padding-bottom: 600px; /* space for bottom buttons */
    color: #333;
}
h1 {
    text-align: center;
    color: #2c3e50;
    margin: 20px 0 10px;
}
h1 span {
    font-size: 14px;
    color: #777;
}

/* ===== Messages ===== */
.message {
    text-align: center;
    font-weight: bold;
    margin: 10px auto;
    padding: 10px 15px;
    border-radius: 6px;
    max-width: 600px;
}
.message.add { background: #2ecc71; color: white; }
.message.delete { background: #e74c3c; color: white; }
.message.exists { background: #f1c40f; color: white; }

/* ===== Date Selector ===== */
.date-selector {
    text-align: center;
    margin-bottom: 20px;
}
.date-selector a {
    margin: 0 5px;
    text-decoration: none;
    color: #3498db;
    font-weight: bold;
    padding: 5px 12px;
    border-radius: 6px;
    transition: 0.3s;
}
.date-selector a.active {
    background: #3498db;
    color: white;
}
.date-selector a:hover {
    background: #2980b9;
    color: white;
}

/* ===== Attendance Table ===== */
table {
    width: 90%;
    margin: 0 auto 30px;
    border-collapse: collapse;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    background: #ffffff; /* Ensure table has solid background */
}
th {
    background: #3498db;
    color: white;
    padding: 12px;
    text-transform: uppercase;
}
td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid #eee;
    background: #f9f9f9; /* Neutral default for rows */
}
tr:nth-child(even) td { background: #f1f1f1; }

/* ===== Buttons ===== */
button, input[type="submit"], a.flat-btn {
    cursor: pointer;
    font-weight: bold;
    border: none;
    border-radius: 6px;
    padding: 8px 14px;
    transition: 0.3s;
    text-decoration: none;
    display: inline-block;
}
button.delete-btn { background: #e74c3c; color: white; }
button.delete-btn:hover { background: #c0392b; }
input[type="submit"] { background: #3498db; color: white; }
input[type="submit"]:hover { background: #2980b9; }

/* ===== Add Date Form ===== */
.add-date-wrapper {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}
.add-date-form {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}
.add-date-form input[type="date"] {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* ===== Form Elements ===== */
select, input[type="date"] {
    padding: 8px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
form { margin-bottom: 15px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; }

/* ===== Bottom Button Row ===== */
.bottom-btns {
    position: fixed;
    bottom: 0;
    left: 0;
    width: 100%;
    background: rgba(255,255,255,0.95);
    display: flex;
    justify-content: center;
    gap: 12px;
    padding: 10px 0;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.1);
    z-index: 999;
}
.bottom-btns a {
    flex: 1;
    text-align: center;
    background: #3498db;
    color: white;
    padding: 10px;
    border-radius: 6px;
    font-weight: bold;
    text-decoration: none;
    transition: 0.3s;
}
.bottom-btns a:hover { background: #2980b9; }

/* ===== Responsive ===== */
@media (max-width: 600px) {
    .add-date-form, form { flex-direction: column; }
    .bottom-btns a { font-size: 14px; padding: 8px; }
}
</style>
</head>
<body>

<?php
// Format block name nicely: "BLOCK_1" => "Block 1"
$block_display = str_replace("_", " ", ucfirst(strtolower($block['block_name'])));
?>
<h1>
    <?php echo htmlspecialchars($block_display); ?>
    — <?php echo htmlspecialchars($block['subject']); ?>
    <span>
        (<?php echo date("g:i A", strtotime($block['start_time'])); ?>
        - <?php echo date("g:i A", strtotime($block['end_time'])); ?>)
    </span>
</h1>

<?php if(isset($message)) echo $message; ?>

<!-- Delete Student -->
<form method="post" action="delete_student.php" onsubmit="return confirm('Delete selected student?');">
    <select name="student_id" required>
        <option value="">-- Select Student --</option>
        <?php
        $std = $conn->query("SELECT id, name FROM students WHERE block=$block_id ORDER BY name ASC");
        while ($st = $std->fetch_assoc()) {
            echo "<option value='{$st['id']}'>{$st['name']}</option>";
        }
        ?>
    </select>
    <button type="submit" class="delete-btn">Delete Student</button>
</form>

<!-- Add Date -->
<div class="add-date-wrapper">
    <form method="post" class="add-date-form">
        <input type="hidden" name="block" value="<?php echo $block_id; ?>">
        <label>Add New Attendance Date:</label>
        <input type="date" name="new_date" required>
        <input type="submit" value="Add Date">
    </form>
</div>

<!-- Date Selector -->
<div id="date-selector" class="date-selector"></div>

<!-- Attendance Table -->
<div id="attendance-table"></div>

<script>
let activeDate = '<?php echo $active_date; ?>';
let blockId = '<?php echo $block_id; ?>';

function loadAttendance() {
    fetch(`attendance_table.php?date=${activeDate}&block=${blockId}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById("attendance-table").innerHTML = html;
        });
}

function loadDateSelector() {
    fetch(`date_selector.php?block=${blockId}&active_date=${activeDate}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById("date-selector").innerHTML = html;
        });
}

loadAttendance();
loadDateSelector();

setInterval(() => {
    loadAttendance();
    loadDateSelector();
}, 3000);
</script>

<!-- Bottom Button Row -->
<div class="bottom-btns">
    <a href="block_summary.php?block=<?php echo $block_id; ?>">📊 Block Summary</a>
    <a href="block_settings.php?block=<?php echo $block_id; ?>">⏰ Edit Block</a>
    <a href="add_student.php?from_block=<?php echo $block_id; ?>">+ Add Student</a>
    <a href="index.php">← Back Home</a>
</div>

</body>
</html>

<?php
include "db.php";

$message = "";

// Auto-generate next block name (BLOCK_6, BLOCK_7, etc.)
$nextBlock = $conn->query("SELECT MAX(id) AS max_id FROM blocks")->fetch_assoc();
$next_number = ($nextBlock['max_id'] ?? 0) + 1;
$auto_block_name = "BLOCK_" . $next_number;

if (isset($_POST['add'])) {
    $block_name = mysqli_real_escape_string($conn, $_POST['block_name']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    // Prevent duplicate block names
    $check = $conn->query("SELECT id FROM blocks WHERE block_name='$block_name' LIMIT 1");

    if ($check->num_rows > 0) {
        $message = "<div class='msg error'>Block name already exists!</div>";
    } else {
        mysqli_query($conn, "
            INSERT INTO blocks (block_name, subject, start_time, end_time)
            VALUES ('$block_name', '$subject', '$start', '$end')
        ");
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Add New Block</title>
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #ffe29f, #ffa99f, #ff719a, #a18cd1);
    margin: 0;
    padding: 0;
}

.container {
    max-width: 520px;
    margin: 60px auto;
    background: #ffffff;
    padding: 35px 40px;
    border-radius: 18px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.25);
}

h1 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 25px;
}

label {
    font-weight: bold;
    display: block;
    margin-bottom: 6px;
    color: #333;
}

input {
    width: 100%;
    padding: 12px 14px;
    border-radius: 8px;
    border: 1px solid #ccc;
    margin-bottom: 18px;
    font-size: 15px;
}

input:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52,152,219,0.2);
}

button {
    width: 100%;
    padding: 14px;
    border-radius: 10px;
    border: none;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
    transition: 0.3s;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
}

.back {
    display: block;
    margin-top: 18px;
    text-align: center;
    text-decoration: none;
    font-weight: bold;
    color: #3498db;
}

.back:hover {
    text-decoration: underline;
}

.msg {
    text-align: center;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-weight: bold;
}
.msg.error {
    background: #e74c3c;
    color: white;
}

small {
    color: #777;
}
</style>
</head>

<body>

<div class="container">
    <h1>➕ Add New Block</h1>

    <?php echo $message; ?>

    <form method="post">
        <label>Block Name</label>
        <input type="text" name="block_name" value="<?php echo $auto_block_name; ?>" required>

        <label>Subject</label>
        <input type="text" name="subject" placeholder="e.g. Data Structures" required>

        <label>Start Time</label>
        <input type="time" name="start_time" required>

        <label>End Time</label>
        <input type="time" name="end_time" required>

        <button name="add">Add Block</button>
    </form>

    <a href="index.php" class="back">← Back to Dashboard</a>
</div>

</body>
</html>

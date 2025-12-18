<?php
session_start();
include "db.php";

// Get block ID from GET parameter
$block_id = isset($_GET['from_block']) ? (int)$_GET['from_block'] : 1;

// 🔧 FETCH BLOCK NAME
$blockQ = $conn->query("SELECT block_name FROM blocks WHERE id = $block_id LIMIT 1");
$block = $blockQ->fetch_assoc();
$block_name = $block ? $block['block_name'] : "Unknown Block";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $rfid = trim($_POST['rfid']);

    $stmt = null;

    if (!empty($name) && !empty($rfid)) {

        // Check if student already exists globally (name OR RFID)
        $stmt_check_global = $conn->prepare("
            SELECT id 
            FROM students 
            WHERE name = ? OR rfid = ?
        ");
        $stmt_check_global->bind_param("ss", $name, $rfid);
        $stmt_check_global->execute();
        $stmt_check_global->store_result();

        if ($stmt_check_global->num_rows > 0) {
            $message = "Student name or RFID already exists in another block!";
        } else {
            // Insert new student
            $stmt = $conn->prepare("
                INSERT INTO students (name, rfid, block) 
                VALUES (?, ?, ?)
            ");
            $stmt->bind_param("ssi", $name, $rfid, $block_id);

            if ($stmt->execute()) {
                $message = "Student added successfully!";
            } else {
                $message = "Error adding student: " . $stmt->error;
            }
        }

        $stmt_check_global->close();
        if ($stmt) $stmt->close();

    } else {
        $message = "Please fill in all fields!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Student - <?php echo htmlspecialchars($block_name); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f9f9f9;
            padding: 30px;
        }
        h1 {
            text-align: center;
            color: #2980b9;
        }
        form {
            max-width: 400px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            background: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #2980b9;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>

<body>

<h1>Add Student — <?php echo htmlspecialchars($block_name); ?></h1>

<?php if (isset($message)) echo "<div class='message'>{$message}</div>"; ?>

<form method="post">
    <input type="text" name="name" placeholder="Student Name" required>
    <input type="text" name="rfid" placeholder="RFID Code" required>
    <input type="submit" value="Add Student">
</form>

<a href="block.php?block=<?php echo $block_id; ?>">← Back to <?php echo htmlspecialchars($block_name); ?></a>

</body>
</html>

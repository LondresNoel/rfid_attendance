<?php
include "db.php";

/* =========================
   DELETE BLOCK LOGIC
========================= */
if (isset($_GET['delete_block'])) {
    $delete_id = (int) $_GET['delete_block'];

    // Delete students under this block
    $conn->query("DELETE FROM students WHERE block = $delete_id");

    // Delete block
    $conn->query("DELETE FROM blocks WHERE id = $delete_id");

    header("Location: index.php");
    exit;
}

// Get all blocks
$blocks = $conn->query("SELECT * FROM blocks ORDER BY id ASC");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>RFID Attendance Dashboard</title>
<link rel="stylesheet" href="css/style.css">
<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #ffe29f, #ffa99f, #ff719a, #a18cd1);
    margin: 0;
    padding-bottom: 600px;
    color: #333;
}

h1 {
    text-align: center;
    color: #2c3e50;
    margin: 20px 0 10px;
}

.container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
    padding: 20px;
}

/* CARD */
.card {
    background-color: #fff;
    width: 250px;
    min-height: 170px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.25s, box-shadow 0.25s;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.25);
}

/* CARD CONTENT (CLICKABLE) */
.card-content {
    padding: 20px;
    text-align: center;
    text-decoration: none;
    color: #333;
}

.card-content h2 {
    margin: 10px 0;
    color: #2980b9;
}

.card-content p {
    margin: 0;
    font-size: 14px;
}

/* DELETE BUTTON AREA */
.card-actions {
    padding: 12px;
    border-top: 1px solid #eee;
    text-align: center;
}

.delete-btn {
    background: #e74c3c;
    color: white;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: bold;
    border-radius: 8px;
    text-decoration: none;
    display: inline-block;
    transition: background 0.2s, transform 0.2s;
}

.delete-btn:hover {
    background: #c0392b;
    transform: scale(1.05);
}

/* ADD BLOCK CARD */
.add-card {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
}

.add-card .card-content h2,
.add-card .card-content p {
    color: white;
}

@media screen and (max-width: 768px) {
    .container {
        flex-direction: column;
        align-items: center;
    }
}
</style>
</head>

<body>

<h1>RFID Attendance System</h1>

<div class="container">

<?php while ($b = $blocks->fetch_assoc()): ?>
    <div class="card">

        <!-- CLICKABLE BLOCK CONTENT -->
        <a href="block.php?block=<?php echo $b['id']; ?>" class="card-content">
            <h2><?php echo htmlspecialchars($b['block_name']); ?></h2>
            <p><?php echo htmlspecialchars($b['subject']); ?></p>
        </a>

        <!-- DELETE BUTTON (SEPARATE) -->
        <div class="card-actions">
            <a
                href="?delete_block=<?php echo $b['id']; ?>"
                class="delete-btn"
                onclick="return confirm('Delete <?php echo htmlspecialchars($b['block_name']); ?> and ALL students inside it?');"
            >
                Delete Block
            </a>
        </div>

    </div>
<?php endwhile; ?>

<!-- ADD BLOCK CARD -->
<div class="card add-card">
    <a href="add_block.php" class="card-content">
        <h2>+ Add Block</h2>
        <p>Create a new block</p>
    </a>
</div>

</div>

</body>
</html>

<?php
include "db.php";

$block_id = intval($_GET['block']);
session_start(); // make sure session is started

$block_id = intval($_GET['block']);
$active_date = isset($_GET['date']) 
    ? $_GET['date'] 
    : ($_SESSION["active_date_block_$block_id"] ?? date("Y-m-d"));

// Store in session so your main block.php logic can use it
$_SESSION["active_date_block_$block_id"] = $active_date;


$dates = $conn->query("
    SELECT DISTINCT a.date
    FROM attendance a
    JOIN students s ON a.student_id = s.id
    WHERE s.block = $block_id
    ORDER BY a.date ASC
");
?>

<strong>Select Active Date:</strong><br><br>

<?php while ($d = $dates->fetch_assoc()): ?>
    <a href="block.php?block=<?php echo $block_id; ?>&date=<?php echo $d['date']; ?>"
   style="margin:0 5px;
          text-decoration:none;
          <?php echo ($d['date'] == $active_date) ? 'font-weight:bold;text-decoration:underline;' : ''; ?>">
    <?php echo $d['date']; ?>
</a>

<?php endwhile; ?>

<br><br>

<form method="post"
      onsubmit="return confirm('Delete date <?php echo $active_date; ?> for Block <?php echo $block_id; ?>?');">
    <input type="hidden" name="delete_date" value="<?php echo $active_date; ?>">
    <button class="delete-btn">Delete This Date</button>
</form>

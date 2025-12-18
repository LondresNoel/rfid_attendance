<?php
include "db.php";

if (isset($_POST['save'])) {
  $id = $_POST['id'];
  $start = $_POST['start_time'];
  $end = $_POST['end_time'];

  $subject = mysqli_real_escape_string($conn, $_POST['subject']);

  mysqli_query($conn,
     "UPDATE blocks 
     SET start_time='$start', 
         end_time='$end', 
         subject='$subject'
     WHERE id=$id"
  );
}





$blocks = mysqli_query($conn, "SELECT * FROM blocks ORDER BY id");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Block Time Settings</title>
  <style>
    /* ===== Body & Container ===== */
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #a8edea, #fed6e3);
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 700px;
      margin: 50px auto;
      background: #fff;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    /* ===== Title ===== */
    h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #333;
      font-size: 32px;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    /* ===== Back Button ===== */
    .back-btn {
      display: inline-block;
      margin-bottom: 25px;
      text-decoration: none;
      color: #fff;
      background: #ff6f61;
      padding: 10px 18px;
      border-radius: 6px;
      font-weight: bold;
      transition: 0.3s;
    }

    .back-btn:hover {
      background: #ff3b2f;
    }

    /* ===== Block Cards ===== */
    .block {
      background: linear-gradient(120deg, #89f7fe, #66a6ff);
      border-radius: 10px;
      padding: 20px 25px;
      margin-bottom: 20px;
      color: #000000ff;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      transition: transform 0.2s;
    }

    .block:hover {
      transform: translateY(-4px);
    }

    .block b {
      display: block;
      font-size: 20px;
      margin-bottom: 10px;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
    }

    input[type="time"] {
      padding: 7px 10px;
      border-radius: 5px;
      border: none;
      margin-right: 10px;
      font-weight: bold;
      color: #333;
    }

    input[type="text"] {
      text-align: center;
      padding: 7px 10px;
      width: 100%;
      max-width: 300px;
      border-radius: 20px;
      border: none;
      font-weight: bold;
      color: #333;
    }

    .save-btn {
      padding: 8px 16px;
      background: #ffcb05;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }

    .save-btn:hover {
      background: #f5a623;
      color: #fff;
    }

    /* ===== Responsive ===== */
    @media (max-width: 600px) {
      .container {
        padding: 20px;
      }

      input[type="time"] {
        margin-bottom: 10px;
        width: 100%;
      }

      .save-btn {
        width: 100%;
        margin-top: 10px;
      }
    }




  </style>
</head>
<body>

<div class="container">

<?php
$block_id = isset($_GET['block']) ? (int)$_GET['block'] : 1; // default to 1
$back_url = "block.php?block=" . $block_id;


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



  <h1>Block Time Settings</h1>

  <?php while ($b = mysqli_fetch_assoc($blocks)) { ?>
    <div class="block">
      <form method="POST">
        <b><?php echo $b['block_name']; ?></b>

        Start:
        <input type="time" name="start_time" value="<?php echo $b['start_time']; ?>">

        End:
        <input type="time" name="end_time" value="<?php echo $b['end_time']; ?>">

        <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
        <button class="save-btn" name="save">Save</button>

        <br><br>
        Subject:
        <input type="text" name="subject" value="<?php echo $b['subject']; ?>" required>



      </form>
    </div>
  <?php } ?>

</div>

</body>
</html>

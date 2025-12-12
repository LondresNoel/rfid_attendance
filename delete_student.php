<?php
include "db.php";

if (isset($_POST['student_id'])) {
    $id = intval($_POST['student_id']);

    // delete attendance
    $conn->query("DELETE FROM attendance WHERE student_id=$id");

    // delete student
    $conn->query("DELETE FROM students WHERE id=$id");
}

// return to previous block
if (isset($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: index.php");
}
exit();
?>

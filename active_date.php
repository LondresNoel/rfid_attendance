<?php
session_start();
header('Content-Type: application/json');
echo json_encode(['active_date'=> isset($_SESSION['active_date']) ? $_SESSION['active_date'] : date("Y-m-d")]);
?>
    
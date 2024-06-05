<?php
session_start();

if (isset($_SESSION['connected_id']) == false) {
    header("Location: login.php");
    exit;   
};
?>
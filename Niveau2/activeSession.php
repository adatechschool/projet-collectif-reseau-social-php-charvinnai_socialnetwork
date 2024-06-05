<?php
if (isset($_SESSION['connected_id']) == false) {
    header("Location: login.php");
    exit;   
};

session_start();
?>
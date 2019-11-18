<?php 
    session_start();
    include("../helpers/session.php");
    logout();
    header("location: /admin/login.php");
?>
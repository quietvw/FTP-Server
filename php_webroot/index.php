<?php
session_start();
error_reporting(0); // Remove for later
ini_set('max_execution_time', 0);
ini_set('memory_limit', '1024M');
ini_set('upload_max_filesize','1024M');
ini_set('post_max_size','1000M');
class MyDB extends SQLite3
{
    function __construct()
    {
        $this->open('../database.db');
    }
}
// Initialize Basic Router
if (!isset($_GET["page"])) {
    $page = "home";
} else {
    $page = $_GET["page"];
}


if ($page == "home") {
    if (!isset($_SESSION["authorized"])) {
    require("login.php");
    } else {
    require("panel.php");
    }
 
}
if ($_GET["page"] == "logoff") {
    session_destroy();
    header("Location: /");
    
}


?>
<?php
session_start();
error_reporting(0); // Remove for later
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
<?php
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


if ($page == "home") require("home.php");



?>
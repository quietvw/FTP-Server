<?php
    $myPDO = new PDO('sqlite:../database.db');
$result = $myPDO->query("SELECT * FROM users");
foreach($result as $row)
{
    var_dump($row);
}
?>
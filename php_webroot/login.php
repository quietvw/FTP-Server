
<?php if (isset($_POST["username"])) {
if ($_POST["username"] == "admin" && $_POST["password"] == "admin") {
    $_SESSION["authorized"] = $_POST["username"];
    require("panel.php");
    die();
} else {
    $error = true;
}
}
?>
<form method="POST">
    <?php if (isset($error)) { ?> <br>The username/password is invalid. <br><?php } ?>
   Username: <input type="text" name="username"><br>
   Password: <input type="password" name="password"><br>
    <input type="submit" value="Login">
</form>

<form method="POST">
    <?php if (isset($error)) { ?> <br>The username/password is invalid. <br><?php } ?>
   Username: <input type="text" name="username"><br>
   Password: <input type="password" name="password"><br>
    <input type="submit" value="Login">
</form>
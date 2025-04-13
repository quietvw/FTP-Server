
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
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="assets/root.css">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <title>FTP Server</title>
  </head>

  <body>
      <div class="p-4 flex flex-col justify-center items-center h-screen">
          <form class="bg-white border-1 border-black p-2 shadow-lg shadow-white w-[35%] h-80 font-thin flex flex-col justify-center" method="POST" >
              <?php if (isset($error)) { ?> <br>The username/password is invalid. <br><?php } ?>
          Username <br> <input class="p-1 mt-1 border-1 border-black" type="text" name="username"><br>
          Password <br> <input class="p-1 mt-1 border-1 border-black" type="password" name="password"><br>
              <input class="mt-2 p-2 border-1 bg-blue-100 rounded-md" type="submit" value="Login">
          </form>
      </div>

  </body>
</html>
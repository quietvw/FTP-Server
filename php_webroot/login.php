
<?php if (isset($_POST["username"])) {
    $myPDO = new PDO('sqlite:../database.db');
// Replace these with the actual input values (e.g., from a form)
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Prepare the SQL to avoid SQL injection
$stmt = $myPDO->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
$stmt->execute(['username' => $username, 'password' => $password]);

$user = $stmt->fetch();

if ($user) {
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

    <title>NebulaFTP</title>
  </head>

  <body>
      <div class="p-4 flex flex-col justify-center items-center h-screen">
          <h1 class="absolute text-[50px] text-center font-thin text-white -translate-y-[270%] translate-x-2 tracking-[0.4em] italic">NebulaFTP</h1>
          <form class="bg-white p-2 shadow-lg shadow-black w-[35%] h-80 font-thin flex flex-col justify-center" method="POST" >
              <?php if (isset($error)) { ?> <p class="warningText">The username/password is invalid.</p> <br><?php } ?>
          Username <br> <input class="icon-user p-1 mt-1 border-1 border-black" type="text" name="username"><br>
          Password <br> <input class="p-1 mt-1 border-1 border-black" type="password" name="password"><br>
              <input class="mt-2 p-2 border-1 bg-gradient-to-r from-blue-100 to-green-100  rounded-md font-bold" type="submit" value="Login">
          </form>
      </div>

  </body>
</html>
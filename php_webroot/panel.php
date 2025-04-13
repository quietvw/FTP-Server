<?php
function check_port($host, $port) {
    $connection = @fsockopen($host, $port);
    if (is_resource($connection)) {
      fclose($connection);
      return true;
    } else {
      return false;
    }
  }
  ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="assets/root.css">
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <title>Admin Panel</title>
  </head>
  <body class="bg-gray-100">

    <div class="p-4 h-screen flex flex-col items-center">
      <!-- Title -->
      <h1 class="text-[50px] font-thin text-gray-800 tracking-[0.4em] italic mb-10 mt-4" style="color:white;">Nebula Admin</h1>

      <!-- Admin Card -->
      <div class="bg-white shadow-lg shadow-black w-[80%] max-w-5xl rounded-lg p-6">
        <!-- Welcome -->
        <div class="mb-6">
          <h2 class="text-2xl font-thin">Welcome, <?php echo $_SESSION["authorized"]; ?></h2>
          <?php
    $pdo = new PDO('sqlite:../database.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the authorized username from session
    $username = $_SESSION["authorized"];

    // Prepare and execute SELECT statement
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);

    // Fetch user data into an associative array
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

          ?>
          <p class="text-gray-600">Manage your FTP server settings and users from here.</p>
        </div>

        <!-- Stats / Controls Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Server Status -->
          <div class="p-4 border border-gray-300 rounded-md">
            <h3 class="font-semibold text-lg mb-2">Server Status</h3>
           <?php
           $myPDO = new PDO('sqlite:../database.db');
            $settingsResult = $myPDO->query("SELECT setting, value FROM settings");
            $settings = [];
            foreach ($settingsResult as $row) {
                $settings[$row['setting']] = $row['value'];
            }
            if (check_port("127.0.0.1",$settings['Port'])) {
                echo '<p class="text-green-600">Online</p>';
            } else {
                echo '<p class="text-red-600">Offline</p>';
            }
            
            ?>
            <p class="text-sm text-gray-500">Last checked: just now</p>
          </div>

     <!-- User Management -->
<div class="p-4 border border-gray-300 rounded-md">
  <h3 class="font-semibold text-lg mb-2">Users</h3>
  <?php if ($userData["permissions"] == "full") { ?>
  <?php

    // Connect to SQLite database
    if (isset($_POST["addUsername"]) && $userData["permissions"] == "full") {
        if ($_POST["addPassword"] == $_POST["addRepeatPassword"]) {
    $pdo = new PDO('sqlite:../database.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    // Prepare and execute insert statement
    $stmt = $pdo->prepare('INSERT INTO users (username, password, path, permissions) VALUES (?, ?, ?, ?)');
    $stmt->execute([$_POST["addUsername"], $_POST["addPassword"], $_POST["addUsername"], $_POST["addPermissions"]]);

    echo '<p class="mb-2 text-sm text-green-500 italic">New User added</p>';
        } else {
            echo '<p class="mb-2 text-sm text-red-500 italic">Password entered does not match.</p>';
        }
    }
if (isset($_GET["deleteuser"]) && isset($_POST["user_id"]) && $userData["permissions"] == "full") {
        // Connect to SQLite database
        $pdo = new PDO('sqlite:../database.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
 
    
        // Prepare and execute delete statement
        $stmt = $pdo->prepare('DELETE FROM users WHERE username = ?');
        $stmt->execute([$_POST["user_id"]]);
    
        if ($stmt->rowCount() > 0) {
            echo '<p class="mb-2 text-sm text-green-500 italic">' . "User '" . $_POST["user_id"] . "' deleted successfully.</p>";
        }
    
}
    ?>
  <table class="w-full text-sm text-gray-700">
    <thead>
      <tr class="text-left border-b border-gray-300">
        <th class="py-2">Username</th>
        <th class="py-2">Permission Level</th>
        <th class="py-2">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $myPDO = new PDO('sqlite:../database.db');
      $result = $myPDO->query("SELECT * FROM users");
      foreach ($result as $row) {
          echo '<tr>';
          echo '<td class="py-2">' . htmlspecialchars($row["username"]) . '</td>';
          echo '<td class="py-2">' . htmlspecialchars($row["permissions"]) . '</td>';
          if ($row["username"] == $_SESSION["authorized"]) {
            echo '<td class="py-2"></td>';
          } else {
          echo '<td class="py-2">
                  <form method="POST" action="?deleteuser" onsubmit="return confirm(\'Are you sure you want to delete this user?\');">
                    <input type="hidden" name="user_id" value="' . $row["username"] . '">
                    <button type="submit" class="px-3 py-1 bg-red-200 text-red-800 rounded hover:bg-red-300">Delete</button>
                  </form>
                </td>';
          }
          echo '</tr>';
      }
      ?>
    </tbody>
  </table>
  <h3 class="font-semibold text-lg mb-2">Add User</h3>

  <form method="POST" class="space-y-3">
    <div>
      <label class="block text-sm font-medium">Username</label>
      <input type="text" name="addUsername" placeholder="New Uername" class="w-full p-2 border border-gray-300 rounded-md">
    </div>
  
    <div>
      <label class="block text-sm font-medium">New Password</label>
      <input type="password" name="addPassword" class="w-full p-2 border border-gray-300 rounded-md" placeholder="New password">
    </div>
    <div>

      <label class="block text-sm font-medium">Retype Password</label>
      <input type="password" name="addRepeatPassword" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Retype password">
    </div>
    <div>
  <label class="block text-sm font-medium">Permissions</label>
  <select name="addPermissions" class="w-full p-2 border border-gray-300 rounded-md">
    <option value="full">Full Permissions</option>
    <option value="user">User Permissions</option>
  </select>
</div>

    <button class="mt-4 w-full py-2 bg-green-200 hover:bg-green-300 text-sm rounded-md font-semibold">Add User</button>
  </form>
  <?php } else { 
    echo '<p class="mb-2 text-sm text-red-500 italic">' . "No Permission to Manage Users</p>";
     } ?>
</div>


      

          <?php
$myPDO = new PDO('sqlite:../database.db');
$myPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Helper: Insert or update setting
function upsertSetting($pdo, $setting, $value) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM settings WHERE setting = :setting");
    $stmt->execute(['setting' => $setting]);
    if ($stmt->fetchColumn()) {
        $update = $pdo->prepare("UPDATE settings SET value = :value WHERE setting = :setting");
        $update->execute(['value' => $value, 'setting' => $setting]);
    } else {
        $insert = $pdo->prepare("INSERT INTO settings (setting, value) VALUES (:setting, :value)");
        $insert->execute(['setting' => $setting, 'value' => $value]);
    }
}

$passwordMessage = null;

// Handle form submission
if (isset($_POST['Host'])) {
    $ip = $_POST['Host'] ?? '';
    $port = $_POST['Port'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    if ($userData["permissions"] == "full") { 
    // Update settings
    upsertSetting($myPDO, 'Host', $ip);
    upsertSetting($myPDO, 'Port', $port);
    $passwordMessage = "Password/Settings updated successfully.";
    }
    
    // Update admin password if entered
    if (!empty($newPassword)) {
        if ($newPassword === $confirmPassword) {
            $hashedPassword = $newPassword;
            $stmt = $myPDO->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
            $stmt->execute(['password' => $hashedPassword]);
            if ($userData["permissions"] == "full") { 
            $passwordMessage = "Password/Settings updated successfully.";
            } else {
                $passwordMessage = "Password updated successfully.";   
            }
        } else {
            $passwordMessage = "Passwords do not match.";
        }
    }
}

// Fetch settings
$settingsResult = $myPDO->query("SELECT setting, value FROM settings");
$settings = [];
foreach ($settingsResult as $row) {
    $settings[$row['setting']] = $row['value'];
}
?>


<!-- Admin Settings Panel -->
<div class="p-4 border border-gray-300 rounded-md">
  <h3 class="font-semibold text-lg mb-2">Server Settings</h3>
  <p class="text-sm text-gray-700 mb-4">Change server settings and password below.</p>

  <?php if (isset($passwordMessage)) { ?>
    <p class="mb-2 text-sm text-red-500 italic"><?php echo htmlspecialchars($passwordMessage); ?></p>
  <?php } ?>

  <form method="POST" class="space-y-3">
    <div>
      <label class="block text-sm font-medium">Listen IP</label>
      <input type="text" name="Host" value="<?php echo htmlspecialchars($settings['Host'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-md" <?php if ($userData["permissions"] != "full") echo "disabled style='background-color:darkgrey;'"; ?>>
    </div>
    <div>
      <label class="block text-sm font-medium">Listen Port</label>
      <input type="text" name="Port" value="<?php echo htmlspecialchars($settings['Port'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-md" <?php if ($userData["permissions"] != "full") echo "disabled style='background-color:darkgrey;'"; ?>>
    </div>
    <div>
      <label class="block text-sm font-medium">New Password <span class="text-gray-400 text-xs">(optional)</span></label>
      <input type="password" name="new_password" class="w-full p-2 border border-gray-300 rounded-md" placeholder="New password">
    </div>
    <div>
      <label class="block text-sm font-medium">Confirm Password</label>
      <input type="password" name="confirm_password" class="w-full p-2 border border-gray-300 rounded-md">
    </div>
    <button class="mt-4 w-full py-2 bg-green-200 hover:bg-green-300 text-sm rounded-md font-semibold">Save Settings</button>
  </form>
  </div></div>


        <!-- Logout Button -->
        <div class="mt-10 text-center">
          <form method="POST" action="?page=logoff">
            <input type="submit" value="Logout" class="px-6 py-2 bg-red-200 hover:bg-red-300 rounded-md font-semibold">
          </form>
        </div>
      </div>
    </div>

  </body>
</html>

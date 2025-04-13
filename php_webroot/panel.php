<?php
session_start();

// Check session
if (!isset($_SESSION["authorized"])) {
    die("Unauthorized access.");
}

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
  <!-- Font Awesome CDN -->
<link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
/>

  <title>NebulaFTP Admin</title>
</head>
<body class="bg-gray-100">
  <div class="p-4 h-screen flex flex-col items-center">
    <h1 class="text-[50px] font-thin text-gray-800 tracking-[0.4em] italic mb-10 mt-4" style="color:white;">NebulaFTP Admin</h1>
    
    <div class="bg-white shadow-lg shadow-black w-[80%] max-w-5xl rounded-lg p-6">
      <div class="mb-6">
        <h2 class="text-2xl font-thin">Welcome, <?php echo htmlspecialchars($_SESSION["authorized"]); ?></h2>
        <?php
        $pdo = new PDO('sqlite:../database.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $username = $_SESSION["authorized"];
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <p class="text-gray-600">Manage your FTP server settings and users from here.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Server Status -->
        <div class="p-4 border border-gray-300 rounded-md">
          <h3 class="font-semibold text-lg mb-2">Server Status</h3>
          <?php
          $settingsResult = $pdo->query("SELECT setting, value FROM settings");
          $settings = [];
          foreach ($settingsResult as $row) {
              $settings[$row['setting']] = $row['value'];
          }
          echo check_port("host.docker.internal", $settings['Port']) 
              ? '<p class="text-green-600"><i class="fas fa-check"></i> Online</p>' 
              : '<p class="text-red-600"><i class="fas fa-times"></i> Offline</p>';
          ?>
          <p class="text-sm text-gray-500">Last checked: just now<br>Storage Usage: <?php
          function foldersize($path) {
            $total_size = 0;
            $files = scandir($path);
            $cleanPath = rtrim($path, '/'). '/';
        
            foreach($files as $t) {
                if ($t<>"." && $t<>"..") {
                    $currentFile = $cleanPath . $t;
                    if (is_dir($currentFile)) {
                        $size = foldersize($currentFile);
                        $total_size += $size;
                    }
                    else {
                        $size = filesize($currentFile);
                        $total_size += $size;
                    }
                }   
            }
        
            return $total_size;
        }
        
        
        function format_size($size) {
            global $units;
        
            $mod = 1024;
        
            for ($i = 0; $size > $mod; $i++) {
                $size /= $mod;
            }
        
            $endIndex = strpos($size, ".")+3;
        
            return substr( $size, 0, $endIndex).' '.$units[$i];
        }
        
           $units = explode(' ', 'B KB MB GB TB PB');
          echo format_size(foldersize("../root"));

          ?></p>

          <?php
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

          if (isset($_POST['Host']) || isset($_POST['new_password'])) {
              $ip = $_POST['Host'] ?? '';
              $port = $_POST['Port'] ?? '';
              $newPassword = $_POST['new_password'] ?? '';
              $confirmPassword = $_POST['confirm_password'] ?? '';

              if ($userData["permissions"] == "full") {
                  upsertSetting($pdo, 'Host', $ip);
                  upsertSetting($pdo, 'Port', $port);
                  $settingsResult = $pdo->query("SELECT setting, value FROM settings");
                  $settings = [];
                  foreach ($settingsResult as $row) {
                      $settings[$row['setting']] = $row['value'];
                  }
              }

              if (!empty($newPassword)) {
                  if ($newPassword === $confirmPassword) {
                      $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = '" . $_SESSION["authorized"] . "'");
                      $stmt->execute(['password' => $newPassword]);
                      $passwordMessage = $userData["permissions"] == "full" ? 
                          "Password/Settings updated successfully." : "Password updated successfully.";
                  } else {
                      $passwordMessage = "Passwords do not match.";
                  }
              }
          }
          ?>
          <br><br>
          <h3 class="font-semibold text-lg mb-2">Server Settings</h3>
          <p class="text-sm text-gray-700 mb-4">Change server settings and password below.</p>

          <?php if ($passwordMessage): ?>
            <p class="mb-2 text-sm text-red-500 italic"><?php echo htmlspecialchars($passwordMessage); ?></p>
          <?php endif; ?>

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
            <button class="mt-4 w-full py-2 bg-green-200 hover:bg-green-300 text-sm rounded-md font-semibold"><i class="fas fa-save"></i> Save Settings</button>
          </form>
        </div>

        <!-- User Management -->
        <div class="p-4 border border-gray-300 rounded-md">
          <h3 class="font-semibold text-lg mb-2">Users</h3>
          <?php
          if ($userData["permissions"] == "full") {
              if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["addUsername"])) {
                  if ($_POST["addPassword"] == $_POST["addRepeatPassword"]) {
                      $stmt = $pdo->prepare('INSERT INTO users (username, password, path, permissions) VALUES (?, ?, ?, ?)');
                      $stmt->execute([$_POST["addUsername"], $_POST["addPassword"], $_POST["addUsername"], $_POST["addPermissions"]]);
                      echo '<p class="mb-2 text-sm text-green-500 italic">New User added</p>';
                  } else {
                      echo '<p class="mb-2 text-sm text-red-500 italic">Password entered does not match.</p>';
                  }
              }

              if (isset($_GET["deleteuser"]) && isset($_POST["user_id"])) {
                  $stmt = $pdo->prepare('DELETE FROM users WHERE username = ?');
                  $stmt->execute([$_POST["user_id"]]);
                  if ($stmt->rowCount() > 0) {
                      echo '<p class="mb-2 text-sm text-green-500 italic">User "' . htmlspecialchars($_POST["user_id"]) . '" deleted successfully.</p>';
                  }
              }

              $result = $pdo->query("SELECT * FROM users");
              echo '<table class="w-full text-sm text-gray-700">
                  <thead>
                      <tr class="text-left border-b border-gray-300">
                          <th class="py-2">Username</th>
                          <th class="py-2">Permission Level</th>
                          <th class="py-2">Action</th>
                      </tr>
                  </thead>
                  <tbody>';
              foreach ($result as $row) {
                  echo '<tr>';
                  echo '<td class="py-2"><i class="fas fa-user"></i> ' . htmlspecialchars($row["username"]) . '</td>';
                  echo '<td class="py-2">' . htmlspecialchars($row["permissions"]) . '</td>';
                  echo '<td class="py-2">';
                  if ($row["username"] != $_SESSION["authorized"]) {
                      echo '<form method="POST" action="?deleteuser" onsubmit="return confirm(\'Are you sure you want to delete this user?\');">
                              <input type="hidden" name="user_id" value="' . $row["username"] . '">
                              <button type="submit" class="px-3 py-1 bg-red-200 text-red-800 rounded hover:bg-red-300"><i class="fas fa-minus"></i> Delete</button>
                          </form>';
                  }
                  echo '</td></tr>';
              }
              echo '</tbody></table>';
              ?>

              <h3 class="font-semibold text-lg mb-2 mt-6">Add User</h3>
              <form method="POST" class="space-y-3">
                <div>
                  <label class="block text-sm font-medium">Username</label>
                  <input type="text" name="addUsername" placeholder="New Username" class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                  <label class="block text-sm font-medium">New Password</label>
                  <input type="password" name="addPassword" class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                  <label class="block text-sm font-medium">Retype Password</label>
                  <input type="password" name="addRepeatPassword" class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div>
                  <label class="block text-sm font-medium">Permissions</label>
                  <select name="addPermissions" class="w-full p-2 border border-gray-300 rounded-md">
                    <option value="full">Full Permissions</option>
                    <option value="user">User Permissions</option>
                  </select>
                </div>
                <button class="mt-4 w-full py-2 bg-green-200 hover:bg-green-300 text-sm rounded-md font-semibold"><i class="fas fa-user-plus"></i> Add User</button>
              </form>
            <?php } else {
              echo '<p class="mb-2 text-sm text-red-500 italic">No Permission to Manage Users</p>';
            } ?>
        </div>

        <!-- Send File -->

          <div class="p-4 border border-gray-300 rounded-md">
          <h3 class="font-semibold text-lg mb-2 mt-6">Quick Download/Upload</h3>
            <?php 
   if (is_dir('../root/' . $_SESSION["authorized"] . '/')) {
    $files = array_diff(scandir('../root/' . $_SESSION["authorized"] . '/'), array('.', '..'));
    echo "<h3>Files in Upload Directory:</h3><ul>";
    foreach ($files as $file) {
        $encodedFile = urlencode($file);
        if (is_dir('../root/' . $_SESSION["authorized"] . '/' . $file)) {
        echo "<li>" . '<i class="fas fa-folder"></i>' . " <a href='download.php?file=$encodedFile'>$file</a></li>";
        } else {
          echo "<li>" . '<i class="fas fa-file"></i>' . " <a href='download.php?file=$encodedFile'>$file</a></li>"; 
        }
    }
    echo "</ul>";
}

?><br><br>
            <div id="send_file">
              <form action="" method="POST" enctype="multipart/form-data">
                  <label for="File">File: </label>
                  <input class="border-2 border-stone-200 w-23 p-0.61 bg-stone-200 hover:bg-stone-300" type="file" id="File" name="File" required />
                  <input class="bg-green-200 hover:bg-green-300 text-sm rounded-md p-2 font-semibold" type="submit" value="Submit">
              </form>
              <?php
                $myPDO = new PDO('sqlite:../database.db');
             
                // File upload handling
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (isset($_FILES['File']) && $_FILES['File']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = '../root/' . $_SESSION["authorized"] . '/';
                        $filename = basename($_FILES['File']['name']);
                        $targetPath = $uploadDir . $filename;

                        // Make sure the upload directory exists
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        // Check if file already exists, if so, rename it
                        if (file_exists($targetPath)) {
                            $fileInfo = pathinfo($filename);
                            $filename = $fileInfo['filename'] . '_' . time() . '.' . $fileInfo['extension'];
                            $targetPath = $uploadDir . $filename;
                        }

                        // Move uploaded file
                        if (move_uploaded_file($_FILES['File']['tmp_name'], $targetPath)) {
                            echo "<h2>File uploaded successfully!</h2>";
                            $encodedFilename = urlencode($filename);
                            echo "<p><a href='download.php?file=$encodedFilename'>Click here to download $filename</a></p>";
                        } else {
                            echo "<p>Error moving the uploaded file. Please check permissions or try again later.</p>";
                        }
                    } else {
                        echo "<p>No file uploaded or upload error occurred. Error Code: " . $_FILES['File']['error'] . "</p>";
                    }
                }

               
                ?>

            </div>
          </div>
      </div>



      <!-- Logout -->
      <div class="mt-10 text-center">
        <form method="POST" action="?page=logoff">
          <input type="submit" value="Logout" class="px-6 py-2 bg-red-200 hover:bg-red-300 rounded-md font-semibold">
        </form>
      </div>
    </div>
  </div>
</body>
</html>

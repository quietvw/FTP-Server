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
            <ul class="list-disc list-inside text-sm text-gray-700">
            <?php
$myPDO = new PDO('sqlite:../database.db');
$result = $myPDO->query("SELECT * FROM users");
foreach ($result as $row) {
    echo '<li>' . $row["username"] . '</li>';
}
            ?>
            </ul>
            <button class="mt-2 px-4 py-1 bg-blue-200 text-sm rounded-md hover:bg-blue-300">Manage Users</button>
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

    // Update settings
    upsertSetting($myPDO, 'Host', $ip);
    upsertSetting($myPDO, 'Port', $port);
    $passwordMessage = "Password/Settings updated successfully.";
    // Update admin password if entered
    if (!empty($newPassword)) {
        if ($newPassword === $confirmPassword) {
            $hashedPassword = $newPassword;
            $stmt = $myPDO->prepare("UPDATE users SET password = :password WHERE username = 'admin'");
            $stmt->execute(['password' => $hashedPassword]);
            $passwordMessage = "Password/Settings updated successfully.";
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
      <input type="text" name="Host" value="<?php echo htmlspecialchars($settings['Host'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-md">
    </div>
    <div>
      <label class="block text-sm font-medium">Listen Port</label>
      <input type="text" name="Port" value="<?php echo htmlspecialchars($settings['Port'] ?? ''); ?>" class="w-full p-2 border border-gray-300 rounded-md">
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

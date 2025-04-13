<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Send the File</title>
</head>
<body>

<div id="send_file">
    <form action="" method="POST" enctype="multipart/form-data">
        <label for="File">File: </label>
        <input type="file" id="File" name="File" required />
        <input type="submit" value="Submit">
    </form>
</div>

<?php
// Connect to SQLite
$myPDO = new PDO('sqlite:../database.db');

// Example query
$result = $myPDO->query("SELECT * FROM users");
foreach ($result as $row) {
    var_dump($row);
}

// File upload handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['File']) && $_FILES['File']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/root/admin/';
        $filename = basename($_FILES['File']['name']);
        $targetPath = $uploadDir . $filename;

        // Make sure the upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['File']['tmp_name'], $targetPath)) {
            echo "<h2>File uploaded successfully!</h2>";
            $encodedFilename = urlencode($filename);
            echo "<p><a href='download.php?file=$encodedFilename'>Click here to download $filename</a></p>";
        } else {
            echo "<p>Error moving the uploaded file.</p>";
        }
    } else {
        echo "<p>No file uploaded or upload error occurred.</p>";
    }
}
?>
</body>
</html>

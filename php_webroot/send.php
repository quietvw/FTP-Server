<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Send the File</title>
</head>
<body>
<?php
$myPDO = new PDO('sqlite:../database.db');
$result = $myPDO->query("SELECT * FROM users");
foreach ($result as $row) {
    var_dump($row);
}

if (isset($_FILES['File']) && $_FILES['File']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../uploads/root/admin/';
    $filename = basename($_FILES['File']['name']);
    $targetPath = $uploadDir . $filename;

    // Make sure the upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Move uploaded file to desired location
    if (move_uploaded_file($_FILES['File']['tmp_name'], $targetPath)) {
        echo "<h2>File uploaded successfully!</h2>";

        // Use download.php to serve the file with correct MIME type
        $encodedFilename = urlencode($filename);
        echo "<p><a href='download.php?file=$encodedFilename'>Click here to download $filename</a></p>";
    } else {
        echo "<p>Error moving the uploaded file.</p>";
    }
} else {
    echo "<p>No file uploaded or there was an upload error.</p>";
}
?>
</body>
</html>

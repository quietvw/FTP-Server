<?php
if (isset($_GET['file'])) {
    $file = basename($_GET['file']); // prevents path traversal
    $filePath = '../uploads/root/admin/' . $file;

    if (file_exists($filePath)) {
        // Detect and send the correct MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        readfile($filePath);
        exit;
    } else {
        echo "File not found.";
    }
}
?>

<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $uploadDir = '../uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileTmp  = $_FILES['image']['tmp_name'];
    $originalName = basename($_FILES['image']['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $uniqueName = uniqid('img_', true) . '.' . $extension;
    $filePath = $uploadDir . $uniqueName;

    $check = getimagesize($fileTmp);
    if ($check === false) {
        exit('File is not a valid image.');
    }

    if (!in_array($extension, $allowedTypes)) {
        exit('Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.');
    }

    if (move_uploaded_file($fileTmp, $filePath)) {
        $stmt = $pdo->prepare("INSERT INTO images (filename, uploaded_at) VALUES (?, NOW())");
        $stmt->execute([$uniqueName]);

        //header("Location: ../index.html?file=" . urlencode($uniqueName));
        header("Location: ../admin/index.html");
        exit();
    } else {
        exit('Failed to move uploaded file.');
    }
} else {
    exit('No image uploaded.');
}

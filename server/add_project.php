<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$required = ['title', 'category', 'slug'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "$field is required"]);
        exit;
    }
}

$title = trim($_POST['title']);
$category = trim($_POST['category']);
$slug = trim($_POST['slug']);

if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Slug can only contain lowercase letters, numbers and hyphens']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM projects WHERE slug = ?");
$stmt->execute([$slug]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Slug already exists']);
    exit;
}

$uploadDir = '../imgs/projects/';
$imagePath = '';

try {
    if (empty($_FILES['image']['name'])) {
        throw new Exception('Project image is required');
    }

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Only JPG, PNG, and GIF images are allowed');
    }

    if ($file['size'] > $maxSize) {
        throw new Exception('Image size must be less than 5MB');
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $slug . '.' . $extension;
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $imagePath = 'imgs/projects/' . $filename;
    } else {
        throw new Exception('Failed to save uploaded file');
    }

    $stmt = $pdo->prepare("INSERT INTO projects (title, category, image_path, slug) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $category, $imagePath, $slug]);

    echo json_encode([
        'success' => true,
        'message' => 'Project added successfully',
        'projectId' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    if (!empty($imagePath) && file_exists("../$imagePath")) {
        unlink("../$imagePath");
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
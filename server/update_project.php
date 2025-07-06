<?php
require 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid project ID']);
    exit;
}

$id = (int)$_GET['id'];

// Validate required fields
$required = ['title', 'category', 'slug'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "$field is required"]);
        exit;
    }
}

// Process data
$title = trim($_POST['title']);
$category = trim($_POST['category']);
$slug = trim($_POST['slug']);

// Validate slug format
if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Slug can only contain lowercase letters, numbers and hyphens']);
    exit;
}

try {
    // 1. Get current project data
    $stmt = $pdo->prepare("SELECT image_path, slug FROM projects WHERE id = ?");
    $stmt->execute([$id]);
    $project = $stmt->fetch();

    if (!$project) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Project not found']);
        exit;
    }

    // 2. Check if new slug conflicts with other projects
    if ($slug !== $project['slug']) {
        $stmt = $pdo->prepare("SELECT id FROM projects WHERE slug = ? AND id != ?");
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Slug already exists']);
            exit;
        }
    }

    $currentImage = $project['image_path'];
    $newImagePath = $currentImage;

    // 3. Handle new image upload if provided
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../imgs/projects/';
        
        // Validate new image
        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Only JPG, PNG, and GIF images are allowed');
        }

        if ($file['size'] > $maxSize) {
            throw new Exception('Image size must be less than 5MB');
        }

        // Delete old image if it exists
        if ($currentImage && file_exists("../$currentImage")) {
            unlink("../$currentImage");
        }

        // Generate new filename using slug
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $slug . '.' . $extension;
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $newImagePath = 'imgs/projects/' . $filename;
        } else {
            throw new Exception('Failed to save uploaded file');
        }
    }

    // 4. Update project in database
    $stmt = $pdo->prepare("UPDATE projects SET title = ?, category = ?, image_path = ?, slug = ? WHERE id = ?");
    $stmt->execute([$title, $category, $newImagePath, $slug, $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Project updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
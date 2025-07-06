<?php
header('Content-Type: application/json');

require_once 'config.php';

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Missing project ID');
    }

    $projectId = (int)$_GET['id'];
    if ($projectId <= 0) {
        throw new Exception('Invalid project ID');
    }

    $stmt = $pdo->prepare("SELECT id, title, category, image_path, slug FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch();

    if (!$project) {
        throw new Exception('Project not found');
    }

    // Add default values for fields not in your database
    $response = [
        'id' => $project['id'],
        'title' => $project['title'],
        'category' => $project['category'],
        'image_path' => $project['image_path'],
        'slug' => $project['slug']
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
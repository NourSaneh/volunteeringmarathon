<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Only for development

$uploadDir = '../uploads/';
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

try {
    // 1. Validate directory exists
    if (!is_dir($uploadDir)) {
        throw new Exception("Uploads directory not found");
    }

    // 2. Scan directory safely
    $files = scandir($uploadDir);
    if ($files === false) {
        throw new Exception("Failed to read directory");
    }

    // 3. Filter and validate images
    $images = [];
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $filePath = $uploadDir . $file;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        
        // Verify: extension + actual image file
        if (in_array($ext, $allowedExtensions) && is_file($filePath)) {
            $images[] = [
                'filename' => $file,
                'path' => 'uploads/' . $file  // Frontend-accessible path
            ];
        }
    }

    // 4. Return response
    echo json_encode([
        'success' => true,
        'images' => $images,
        'count' => count($images)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
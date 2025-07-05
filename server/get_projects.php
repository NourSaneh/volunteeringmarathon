<?php
require 'config.php'; // DB connection

header('Content-Type: application/json');

$stmt = $pdo->query("SELECT * FROM projects ORDER BY id DESC");
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($projects);

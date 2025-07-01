<?php
session_start();
$timeout = 3600; // 1 hour

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    if (time() - $_SESSION['login_time'] <= $timeout) {
        echo json_encode(['loggedIn' => true]);
        exit;
    } else {
        session_unset();
        session_destroy();
    }
}
echo json_encode(['loggedIn' => false]);
?>

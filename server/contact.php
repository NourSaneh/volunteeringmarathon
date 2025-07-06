<?php
$to = "hatoumkarim33@gmail.com";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = trim($_POST["message"]);

    echo $name . $email . $message;

    if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($message)) {
        header("Location: ../contact.html?status=error");
        exit;
    }

    if (mail($to, $name, "help me")) {
        header("Location: ../contact.html?status=success");
        exit;
    } else {
        header("Location: ../contact.html?status=error");
        exit;
    }
} else {
    header("Location: ../contact.html");
    exit;
}
?>
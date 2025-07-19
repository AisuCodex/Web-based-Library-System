<?php
session_name('user_session');
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['email'])) {
    echo json_encode(['email' => $_SESSION['email']]);
} else {
    echo json_encode(['email' => null]);
}
?>

<?php
session_start();
require_once '../config.php';

$conn = connect();
if (!$conn) {
    $_SESSION['message'] = 'Ошибка подключения к БД';
    header('Location: ../reg.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lastname = trim($_POST['lastname']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $login = trim($_POST['login']);
    $password = $_POST['user_password'];
    
    // Валидация
    if (strlen($password) < 6) {
        $_SESSION['message'] = 'Пароль должен быть не короче 6 символов';
        header('Location: ../reg.php');
        exit;
    }
    
    // Проверка уникальности
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE login = ? OR email = ?");
    mysqli_stmt_bind_param($stmt, "ss", $login, $email);
    mysqli_stmt_execute($stmt);
    
    if (mysqli_stmt_get_result($stmt)->num_rows > 0) {
        $_SESSION['message'] = 'Логин или email уже занят';
        header('Location: ../reg.php');
        exit;
    }
    
    // Хешируем пароль
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Сохраняем пользователя
    $stmt = mysqli_prepare($conn, "INSERT INTO users (lastname, name, username, login, email, password) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssss", $lastname, $name, $username, $login, $email, $hashed_password);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Регистрация успешна! Можете войти.';
    } else {
        $_SESSION['message'] = 'Ошибка регистрации';
    }
}

header('Location: ../reg.php');
exit;
?>

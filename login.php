<?php 
session_start();

if (isset($_SESSION['user'])) {
    header('Location: account.php');
    exit;
}


$ip = $_SERVER['REMOTE_ADDR'];
$max_attempts = 3;
$lockout_time = 300;

if (isset($_SESSION['login_attempts'][$ip]) && $_SESSION['login_attempts'][$ip]['time'] > time()) {
    $wait = ceil(($_SESSION['login_attempts'][$ip]['time'] - time()) / 60);
    $message = "Блокировка! Подождите $wait мин.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($message)) {
    require_once 'config.php';
    $conn = connect();
    
    if ($conn) {
        $login = trim($_POST['login']);
        $password = $_POST['password'];
        
        $stmt = mysqli_prepare($conn, "SELECT id, lastname, name, username, email, password FROM users WHERE login = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $login);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                "id" => $user['id'], 
                "lastname" => $user['lastname'],
                "name" => $user['name'], 
                "username" => $user['username'],
                "email" => $user['email']
            ];
            unset($_SESSION['login_attempts']);
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['login_attempts'][$ip] = [
                'attempts' => ($_SESSION['login_attempts'][$ip]['attempts'] ?? 0) + 1,
                'time' => time() + $lockout_time
            ];
            $message = 'Неверный логин или пароль';
        }
        mysqli_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="login.css">
    <style>@import url('https://fonts.googleapis.com/css2?family=Advent+Pro:ital,wght@0,100..900;1,100..900&family=Shantell+Sans:ital,wght@0,300..800;1,300..800&display=swap');</style>
    <title>Авторизация</title>
</head>
<body>
    <div id="content-container">
        <form method="post" action="">
            <h3>Авторизация</h3>
            <input type="text" name="login" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
            <p>Нет аккаунта? <a href="reg.php">Зарегистрироваться</a></p> 
            <?php if (isset($message)): ?>
                <p style="color:#d63131; font-size: 18pt"><?php echo $message; ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>

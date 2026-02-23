<?php 
    session_start();
    
    if ($_SESSION['user']){
        header('Location: order.php');
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="reg.css"> 
    <title>Регистрация</title>
     <style> @import url('https://fonts.googleapis.com/css2?family=Advent+Pro:ital,wght@0,100..900;1,100..900&family=Shantell+Sans:ital,wght@0,300..800;1,300..800&display=swap');
</style>
</head>
<body>
    <div id="content-container">
    <form method="post" action="src/register.php"> 
        <h3>Регистрация</h3>
        <input type="text" name="lastname" placeholder="Фамилия" required>
        <input type="text" name="name" placeholder="Имя" required>
        <input type="email" name="email" placeholder="Электронная почта" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="text" name="login" placeholder="Логин" pattern="^[a-zA-Z0-9_]+$" title="Только буквы, цифры и подчёркивания" required>
        <input type="password" name="user_password" placeholder="Пароль" required>
        <button type="submit">Зарегистрироваться</button>
        <p>Уже есть аккуант? <a href="login.php">Войти</a></p> 
                <?php 
                    if( $_SESSION['message']){
                        echo '<p style="color:#d63131; font-size: 18pt" class="msg">' . $_SESSION['message'] . '</p>';
                    }
                    unset($_SESSION['message']);
                ?>
    </form>
</div>
      <div class="player"> 
            <iframe src="/player.html" width="100%" height="100%" frameborder="0" scrolling="no" allow="autoplay"></iframe>
        </div>
</body>
</html>
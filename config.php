<?php
function connect() {
    $host = 'localhost';
    $db = 'coffee_db';
    $user = 'root';
    $pass = 'root';  
    
    $connect = mysqli_connect($host, $user, $pass, $db);
    
    if (!$connect) {
        die("Ошибка подключения: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($connect, "utf8mb4");
    return $connect;
}
?>

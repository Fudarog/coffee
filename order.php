
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="order.css">
    <title>Заказ</title>
</head>
<body>
    <header>
        <nav>
           <h1><a href="index.php">Coffee Break</a></h1>
            <ul>
                <li><a href="admin.php">Админ-панель</a></li>
            </ul>
        </nav>
        <div class="line"></div>
    </header>
    <main>
    <section class="first-section">
        <input type="date" name="date" placeholder="Дата" required>
        <input type="time" name="date" placeholder="Время" required>
        <input type="number" name="date" placeholder="Количество человек" required>
        <textarea placeholder="Особые пожелания"></textarea>
        <button>Забронировать</button>
    </section>
    
    <section class="second-section">
        <input type="text" name="date" placeholder="Имя">
        <input type="tel" id="phone" name="phone" placeholder="+7 (999) 999-99-99" required>
        <div class="order-place">
            <p>Что хотите заказать?</p>
            <button type="button">Выбрать</button>
            <?php
            ?>
        </div>
        <div class="order-end">
            <p>Самовывоз</p>
            <button type="submit">Заказать</button>
        </div>
    </section>
    </main>
</body>
</html>
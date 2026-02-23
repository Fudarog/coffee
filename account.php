<?php 
session_start(); 
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$conn = connect();
$success_message = null;

// Загружаем корзину ✅ ИСПРАВЛЕНО
$stmt = mysqli_prepare($conn, "
    SELECT c.id, c.quantity, m.id as menu_id, m.name, m.price, m.image 
    FROM cart c JOIN menu m ON c.menu_id = m.id 
    WHERE c.user_id = ? AND c.status = 'active'
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$cart_items = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['quantity'] * $item['price'];
}

// === 1. ОБРАБОТКА ЗАКАЗА ✅ РАБОТАЕТ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_type'])) {
    $order_type = $_POST['order_type'];
    $phone = trim($_POST['phone']);
    $special_requests = trim($_POST['special_requests'] ?? '');
    
    // Создаем заказ
    $stmt = mysqli_prepare($conn, "
        INSERT INTO orders (user_id, name, phone, order_type, items, special_requests, total_price, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'new')
    ");
    $items_json = json_encode($cart_items);
    mysqli_stmt_bind_param($stmt, "isssdsd", $user_id, $_SESSION['user']['name'], $phone, $order_type, $items_json, $special_requests, $total);
    mysqli_stmt_execute($stmt);
    $order_id = mysqli_insert_id($conn);
    
    // ✅ ТОВАРЫ ЗАКАЗА - РАБОТАЕТ
    foreach ($cart_items as $cart_item) {
        $menu_id = $cart_item['menu_id'];  // из SELECT выше
        $quantity = $cart_item['quantity'];
        $price = $cart_item['price'];
        
        $stmt_item = mysqli_prepare($conn, "INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_item, "iiid", $order_id, $menu_id, $quantity, $price);
        mysqli_stmt_execute($stmt_item);
        mysqli_stmt_close($stmt_item);
    }
    
    // Очищаем корзину
    $stmt_clear = mysqli_prepare($conn, "UPDATE cart SET status = 'ordered' WHERE user_id = ? AND status = 'active'");
    mysqli_stmt_bind_param($stmt_clear, "i", $user_id);
    mysqli_stmt_execute($stmt_clear);
    
    $success_message = "Заказ #$order_id успешно создан! Мы свяжемся с вами по телефону $phone";
    $cart_items = []; // Очищаем отображение
    $total = 0;
}

// === 2. УДАЛЕНИЕ ИЗ КОРЗИНЫ ✅ РАБОТАЕТ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'remove' && isset($_POST['cart_id'])) {
        $stmt = mysqli_prepare($conn, "DELETE FROM cart WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, "ii", $_POST['cart_id'], $user_id);
        mysqli_stmt_execute($stmt);
    }
    if ($action === 'clear') {
        $stmt = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ? AND status = 'active'");
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
    }
    header('Location: account.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="account.css">
    <title>Аккаунт - Coffee Break</title>
</head>
<body>
    <header>
        <nav>
            <h1><a href="index.php">Coffee Break</a></h1>
            <ul>
                <li><a href="account.php" class="active">Аккаунт</a></li>
                <li><a href="logout.php">Выход</a></li>
            </ul>
        </nav>
    </header>

    <main class="account-main">
        <div class="account-container">
            <!-- ✅ УСПЕШНЫЙ ЗАКАЗ -->
            <?php if ($success_message): ?>
                <div class="success-message">
                    <h2>✅ <?php echo $success_message; ?></h2>
                    <a href="index.php#menu" class="btn">Новый заказ ☕</a>
                </div>
            <?php else: ?>
            
            <!-- ✅ КОРЗИНА -->
            <section class="cart-section">
                <h2>🛒 Корзина (<?php echo count($cart_items); ?>)</h2>
                
                <?php if (empty($cart_items)): ?>
                    <div class="empty-cart">
                        <p>Корзина пуста</p>
                        <a href="index.php#menu" class="btn">Выбрать кофе ☕</a>
                    </div>
                <?php else: ?>
                    <div class="cart-items">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <img src="<?php echo htmlspecialchars($item['image'] ?: 'img/no-image.png'); ?>" width="60" height="60" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="item-info">
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p><?php echo $item['quantity']; ?> × <?php echo number_format($item['price'], 0, ',', ' '); ?> ₽</p>
                            </div>
                            <div class="item-total"><?php echo number_format($item['quantity'] * $item['price'], 0, ',', ' '); ?> ₽</div>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="remove-btn">Удалить ❌</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="cart-total">
                            <h3>Итого: <?php echo number_format($total, 0, ',', ' '); ?> ₽</h3>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="clear-btn">Очистить корзину 🗑️</button>
                            </form>
                        </div>

                        <!-- ✅ ФОРМА ЗАКАЗА -->
                        <div class="order-form">
                            <h3>📋 Оформить заказ</h3>
                            <form method="post">
                                <div class="order-row">
                                    <select name="order_type" required>
                                        <option value="">Выберите тип заказа</option>
                                        <option value="table_booking">Забронировать столик</option>
                                        <option value="pickup">Самовывоз</option>
                                        <option value="delivery">Доставка</option>
                                    </select>
                                    <input type="tel" name="phone" placeholder="+7 (999) 999-99-99" required>
                                </div>
                                <textarea name="special_requests" placeholder="Особые пожелания (необязательно)"></textarea>
                                <button type="submit" class="checkout-btn">✅ Подтвердить заказ</button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

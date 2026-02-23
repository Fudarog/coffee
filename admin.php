<?php 
session_start(); 
require_once 'config.php';

$conn = connect();

if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Проверка авторизации админа
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_pass'])) {
        if ($_POST['admin_pass'] === 'admin123') { // Измените пароль!
            $_SESSION['admin'] = true;
        } else {
            $error = "❌ Неверный пароль";
        }
    }
    if (!isset($_SESSION['admin'])) {
    
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <style>
        body { font-family: Arial; background: #492F1E; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login { background: rgba(255,255,255,0.1); padding: 40px; border-radius: 15px; backdrop-filter: blur(10px); }
        input { width: 100%; padding: 15px; margin: 10px 0; border: none; border-radius: 10px; font-size: 16px; }
        button { width: 100%; padding: 15px; background: #F1DAAE; color: #492F1E; border: none; border-radius: 10px; font-size: 18px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <div class="login">
        <h2>🔐 Админ панель</h2>
        <?php if (isset($error)) echo "<p style='color: #ff4444;'>$error</p>"; ?>
        <form method="POST">
            <input type="password" name="admin_pass" placeholder="Пароль админа" required>
            <button type="submit">Войти</button>
        </form>
        <p style="margin-top: 20px; opacity: 0.8;">По умолчанию: <strong>admin123</strong></p>
    </div>
</body>
</html>
<?php
        exit;
    }
}

$table = $_GET['table'] ?? 'menu';
$action = $_POST['action'] ?? '';

if ($action && in_array($table, ['users', 'menu', 'orders', 'cart', 'order_items'])) {
    if ($action === 'add') {
        $fields = $_POST['fields'];
        $values = array_values($fields);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        $stmt = mysqli_prepare($conn, "INSERT INTO `$table` (" . implode(',', array_keys($fields)) . ") VALUES ($placeholders)");
        mysqli_stmt_bind_param($stmt, str_repeat('s', count($values)), ...$values);
        mysqli_stmt_execute($stmt);
    }
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $stmt = mysqli_prepare($conn, "DELETE FROM `$table` WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $_POST['id']);
        mysqli_stmt_execute($stmt);
    }
    
    if ($action === 'edit' && isset($_POST['id'], $_POST['fields'])) {
        $fields = $_POST['fields'];
        $set = [];
        $params = [];
        foreach ($fields as $key => $value) {
            $set[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $_POST['id'];
        $stmt = mysqli_prepare($conn, "UPDATE `$table` SET " . implode(',', $set) . " WHERE id = ?");
        mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...$params);
        mysqli_stmt_execute($stmt);
    }
    header('Location: admin.php?table=' . $table);
    exit;
}
$tables = ['users', 'menu', 'orders', 'cart', 'order_items'];
$result = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) $tables[] = $row[0];

$data = [];
if (in_array($table, $tables)) {
    $result = mysqli_query($conn, "SELECT * FROM `$table`");
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $fields_result = mysqli_query($conn, "DESCRIBE `$table`");
    $fields = mysqli_fetch_all($fields_result, MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Coffee Break</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Advent+Pro:ital,wght@0,100..900;1,100..900&family=Shantell+Sans:ital,wght@0,300..800;1,300..800&family=Unbounded:wght@200..900&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Advent+Pro:ital,wght@0,100..900;1,100..900&family=Great+Vibes&family=Shantell+Sans:ital,wght@0,300..800;1,300..800&family=Unbounded:wght@200..900&display=swap');

        * { font-family: 'Unbounded', Arial, sans-serif; margin: 0; padding: 0; box-sizing: border-box; }
        body { background: linear-gradient(135deg, #492F1E 0%, #6B4E31 100%); color: white; min-height: 100vh; }
        .header { background: rgba(0,0,0,0.2); padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 28px; }
        .logout { background: #ff4444; color: white; padding: 10px 20px; border: none; border-radius: 25px; cursor: pointer; }
        .container { max-width: 1400px; margin: 0 auto; padding: 40px 20px; }
        .tables-nav { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 30px; }
        .table-btn { background: rgba(255,255,255,0.1); color: white; padding: 12px 20px; border: 2px solid #F1DAAE; border-radius: 25px; text-decoration: none; transition: all 0.3s; }
        .table-btn.active, .table-btn:hover { background: #F1DAAE; color: #492F1E; }
        .crud-section { background: rgba(255,255,255,0.05); padding: 30px; border-radius: 20px; backdrop-filter: blur(10px); }
        .add-form { background: rgba(255,255,255,0.1); padding: 25px; border-radius: 15px; margin-bottom: 30px; }
        .form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
        input, textarea, select { padding: 12px; border: 2px solid #F1DAAE; border-radius: 10px; background: white; font-size: 14px; }
        .btn { padding: 12px 24px; border: none; border-radius: 25px; font-weight: bold; cursor: pointer; transition: all 0.3s; font-size: 14px; }
        .btn-primary { background: #F1DAAE; color: #492F1E; border: 2px solid #D4A574; }
        .btn-danger { background: #ff4444; color: white; }
        .btn-success { background: #28a745; color: white; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; background: rgba(255,255,255,0.1); border-radius: 15px; overflow: hidden; }
        .table th, .table td { padding: 15px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.2); }
        .table th { background: rgba(0,0,0,0.3); }
        .table tr:hover { background: rgba(255,255,255,0.1); }
        .edit-btn, .delete-btn { padding: 8px 15px; margin: 0 5px; border-radius: 20px; font-size: 12px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; }
        .modal-content { background: white; color: #333; margin: 5% auto; padding: 30px; border-radius: 15px; width: 90%; max-width: 600px; max-height: 80vh; overflow-y: auto; }
        .close { float: right; font-size: 30px; cursor: pointer; color: #999; }
        @media (max-width: 768px) { .container { padding: 20px 10px; } .form-row { grid-template-columns: 1fr; } }
        /* 📊 СТАТИСТИКА */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    background: rgba(255,255,255,0.1);
    padding: 30px 25px;
    border-radius: 20px;
    text-align: center;
    border: 2px solid rgba(241,218,174,0.3);
    backdrop-filter: blur(15px);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #F1DAAE, #D4A574, #F1DAAE);
    opacity: 0;
    transition: opacity 0.4s;
}

.stat-card:hover {
    transform: translateY(-8px);
    border-color: #F1DAAE;
    box-shadow: 0 25px 50px rgba(0,0,0,0.3);
}

.stat-card:hover::before {
    opacity: 1;
}

.stat-card h3 {
    font-size: 18px;
    margin-bottom: 15px;
    color: #F1DAAE;
    opacity: 0.9;
}

.stat-number {
    font-size: 36px;
    font-weight: 800;
    color: white;
    background: linear-gradient(135deg, #F1DAAE, #D4A574);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 10px;
}

.stat-card.highlight {
    border-color: #28a745;
    background: rgba(40,167,69,0.15);
}

.stat-card.highlight .stat-number {
    background: linear-gradient(135deg, #28a745, #20c997);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* Адаптивность */
@media (max-width: 768px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .stat-number { font-size: 28px; }
}

    </style>
</head>
<body>
    <div class="header" style="background: rgba(0,0,0,0.2); padding: 20px 70px; display: flex; justify-content: space-between; align-items: center;">
    <!-- Левая часть - заголовок -->
    <h1 style="font-family: 'Great Vibes', cursive; font-size: 28pt; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); margin: 0;">Coffee Break - Админ панель</h1>
    
    <!-- Правая часть - кнопки -->
    <div style="display: flex; gap: 15px; align-items: center;">
        <!-- Кнопка НА ГЛАВНУЮ -->
        <a href="index.php" style="background: linear-gradient(135deg, #F1DAAE, #D4A574); color: #492F1E; padding: 12px 25px; border-radius: 25px; text-decoration: none; font-weight: bold; font-size: 16px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(241,218,174,0.4);" 
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(241,218,174,0.6)'" 
           onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(241,218,174,0.4)'">
            Главная
        </a>
        
        <!-- Кнопка ВЫХОД -->
        <a href="?logout=1" class="logout" onclick="return confirm('Выйти из админки?')" 
           style="background: linear-gradient(135deg, #ff4444, #cc3333); color: white; padding: 12px 25px; border-radius: 25px; text-decoration: none; font-weight: bold; font-size: 16px; transition: all 0.3s; box-shadow: 0 4px 15px rgba(255,68,68,0.4);" 
           onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(255,68,68,0.6)'" 
           onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 15px rgba(255,68,68,0.4)'">
            Выход
        </a>
    </div>
</div>

</div>

    
   <div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <h3>👥 Пользователи</h3>
            <div class="stat-number"><?= mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0] ?></div>
        </div>
        <div class="stat-card">
            <h3>☕ Меню</h3>
            <div class="stat-number"><?= mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM menu"))[0] ?></div>
        </div>
        <div class="stat-card">
            <h3>📦 Заказы</h3>
            <div class="stat-number"><?= mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM orders"))[0] ?></div>
        </div>
        <div class="stat-card highlight">
            <h3>💰 Выручка</h3>
            <div class="stat-number"><?= number_format(mysqli_fetch_row(mysqli_query($conn, "SELECT COALESCE(SUM(total_price), 0) FROM orders WHERE status != 'cancelled'"))[0], 0, ',', ' ') ?> ₽</div>
        </div>
        <div class="stat-card">
            <h3>🛒 Корзины</h3>
            <div class="stat-number"><?= mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM cart WHERE status = 'active'"))[0] ?></div>
        </div>
        <div class="stat-card">
            <h3>⭐ Новые сегодня</h3>
            <div class="stat-number"><?= mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()"))[0] ?></div>
        </div>
    </div>

    <div class="tables-nav">
        <?php 
        $main_tables = ['users', 'menu', 'orders', 'cart', 'order_items'];
        foreach ($main_tables as $t): 
        ?>
            <a href="?table=<?= $t ?>" class="table-btn <?= $table === $t ? 'active' : '' ?>">
                <?= ucwords(str_replace('_', ' ', $t)) ?>
            </a>
        <?php endforeach; ?>
    </div>
        
        <div class="crud-section">
            <!-- Форма добавления -->
            <div class="add-form">
                <h3>➕ Добавить запись</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="table" value="<?= $table ?>">
                    <div class="form-row">
                        <?php foreach ($fields as $field): 
                            if ($field['Field'] === 'id') continue;
                        ?>
                            <div>
                                <label><?= ucfirst(str_replace('_', ' ', $field['Field'])) ?>:</label>
                                <input type="text" name="fields[<?= $field['Field'] ?>]" required>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </form>
            </div>
            
            <!-- Таблица данных -->
            <h3>📋 <?= ucfirst($table) ?> (<?= count($data) ?> записей)</h3>
            <table class="table">
                <thead>
                    <tr>
                        <?php foreach ($fields as $field): ?>
                            <th><?= ucfirst(str_replace('_', ' ', $field['Field'])) ?></th>
                        <?php endforeach; ?>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <?php foreach ($fields as $field): ?>
                            <td><?= htmlspecialchars($row[$field['Field']] ?? '') ?></td>
                        <?php endforeach; ?>
                        <td>
                            <button class="edit-btn btn btn-success" onclick="editRow(<?= $row['id'] ?>)">Изменить</button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Удалить?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="table" value="<?= $table ?>">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" class="delete-btn btn btn-danger">Удалить</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Модальное окно редактирования -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>✏️ Редактировать запись</h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="table" value="<?= $table ?>">
                <input type="hidden" name="id" value="">
                <div class="form-row" id="editFields">
                    <!-- Поля заполнятся JS -->
                </div>
                <button type="submit" class="btn btn-primary">Сохранить</button>
            </form>
        </div>
    </div>

    <script>
        function editRow(id) {
            fetch(`?table=<?= $table ?>&get_row=${id}`)
                .then(r => r.json())
                .then(data => {
                    document.querySelector('[name="id"]').value = id;
                    const fields = document.getElementById('editFields');
                    fields.innerHTML = '';
                    for (let key in data) {
                        if (key !== 'id') {
                            fields.innerHTML += `
                                <div>
                                    <label>${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}:</label>
                                    <input type="text" name="fields[${key}]" value="${data[key]}" required>
                                </div>
                            `;
                        }
                    }
                    document.getElementById('editModal').style.display = 'block';
                });
        }
        
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        window.onclick = e => {
            if (e.target.classList.contains('modal')) closeModal();
        }
    </script>
</body>
</html>

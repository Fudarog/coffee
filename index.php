<?php
session_start();

// ✅ ЕДИНСТВЕННЫЙ обработчик добавления в корзину
if (isset($_POST['add_to_cart']) && isset($_SESSION['user'])) {
    require_once 'config.php';
    $conn = connect();
    
    if ($conn) {
        $menu_id = (int)$_POST['add_to_cart'];
        $quantity = (int)($_POST['quantity'] ?? 1);
        $user_id = $_SESSION['user']['id'];
        
        // Проверяем товар
        $stmt = mysqli_prepare($conn, "SELECT id, name, price FROM menu WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $menu_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $menu_item = mysqli_fetch_assoc($result);
        
        if ($menu_item) {
            // Добавляем в корзину
            $stmt = mysqli_prepare($conn, "
                INSERT INTO cart (user_id, menu_id, quantity, status) 
                VALUES (?, ?, ?, 'active')
                ON DUPLICATE KEY UPDATE quantity = quantity + ?
            ");
            mysqli_stmt_bind_param($stmt, "iiii", $user_id, $menu_id, $quantity, $quantity);
            mysqli_stmt_execute($stmt);
            
            $_SESSION['success'] = "✅ Добавлено: {$menu_item['name']} ×{$quantity}";
        }
        mysqli_close($conn);
    }
    header("Location: index.php#menu");
    exit;
}

// Загрузка меню
$menu_items = [];
if (file_exists('config.php')) {
    require_once 'config.php';
    $conn = @connect();
    if ($conn) {
        $stmt = @mysqli_prepare($conn, "SELECT id, name, type, price, description, image FROM menu WHERE is_active = 1");
        if ($stmt) {
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $menu_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        }
        @mysqli_close($conn);
    }
}
?>



<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index.css">
    <meta name="description" content="Кофейня Coffee Break: свежесваренный кофе, выпечка, завтраки. Удобно для кофе-брейка по пути на работу. Заказ онлайн, доставка, акции для постоянных.">
    <meta name="keywords" content="свежий кофе, десерты, кофейня, выпечка, завтрак">
    <title>Coffee Break</title>
</head>

<body>
    <header>
        <nav>
            <h1>Coffee Break</h1>
            <ul>
                <li><?php if (isset($_SESSION['user'])): ?>
                        <a href="account.php">🛒 Корзина</a> | <a href="logout.php">Выход</a>
                    <?php else: ?>
                        <a href="login.php">Войти</a>
                    <?php endif; ?>
                </li>

                <li><a href="admin.php">Админ-панель</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <section class="first-block">
            <p>Кофе + десерт = <br>идеальный момент</p>
            <h3>Coffee Break</h3>
        </section>
        <section id="company" class="second-block">
            <img src="img/coffee.png" width="550px" height="550px">
            <p>Добро пожаловать в Cofee Break
                — уютный<br> уголок в сердце Москвы, где каждый глоток кофе<br> рассказывает историю.
                Мы обжариваем зёрна от <br>лучших ферм мира и варим их вручную, чтобы вы <br>почувствовали настоящий вкус.
                С 8 утра до <br>позднего вечера здесь пахнет свежестью, звучит <br>лёгкая музыка, а наши бариста делятся<br>
                секретами идеального эспрессо. Идеальное <br>место для работы, встреч с друзьями или просто <br>паузы в ритме города.</p>
        </section>

        <section id="menu" class="meny" style="background: #492F1E; padding: 80px 200px; min-height: 600px;">
            <h4 style="text-align: center; font-size: 42px; color: white; margin-bottom: 60px; font-family: unbounded; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">Мы предлагаем</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 40px; max-width: 1400px; margin: 0 auto;">
                <div style="background: rgba(255,255,255,0.95); border: 4px solid #F1DAAE; border-radius: 25px; padding: 35px 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); height: 480px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #F1DAAE, #D4A574, #F1DAAE); opacity: 0; transition: opacity 0.4s;"></div>

                    <div style="width: 150px; height: 150px; margin: 0 auto 25px; border-radius: 50%; overflow: hidden; border: 6px solid white; box-shadow: 0 15px 40px rgba(139,69,19,0.4);">
                        <img src="img/americano.jpg" style="width: 100%; height: 100%; object-fit: cover;"
                            onerror="this.parentElement.innerHTML='<div style=\'background: linear-gradient(135deg, #8B4513, #D2691E); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 55px; color: white;\'>☕</div>'"
                            alt="Американо">
                    </div>
                    <h5 style="font-size: 32px; color: #492F1E; margin-bottom: 10px; font-family: Unbounded, cursive;">Американо</h5>
                    <p style="color: #6B4E31; margin-bottom: 15px; font-size: 16px; line-height: 1.6; min-height: 60px; display: flex; align-items: center; justify-content: center;">Классический вкус из свежемолотых зёрен Италии</p>
                    <div style="font-size: 28px; font-weight: 800; color: #492F1E; margin-bottom: 20px; background: #8A5E2D; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">250 ₽</div>
                    <?php if (isset($_SESSION['user'])): ?>
                    <form method="POST">
                        <input type="hidden" name="add_to_cart" value="1"> 
                        <button type="submit" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #F1DAAE, #E8C59A); color: #492F1E; border: 3px solid #D4A574; border-radius: 40px; font-weight: 700; font-size: 18px; cursor: pointer; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #492F1E, #6B4E31)'; this.style.color='#F1DAAE'; this.style.borderColor='#F1DAAE'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(73,47,30,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #F1DAAE, #E8C59A)'; this.style.color='#492F1E'; this.style.borderColor='#D4A574'; this.style.transform='none'; this.style.boxShadow='none'">В корзину</button>
                    </form>
                   <?php else: ?>
    <a href="login.php" 
       style="width: 100%; padding: 20px; background: linear-gradient(135deg, #3498DB, #2980B9); color: white; text-decoration: none; border-radius: 40px; font-weight: 700; font-size: 18px; display: flex; align-items: center; justify-content: center; font-family: Unbounded, sans-serif; transition: all 0.4s ease; position: relative; overflow: hidden;"
       onmouseover="this.style.background='linear-gradient(135deg, #2980B9, #1F618D)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(52,152,219,0.4)'"
       onmouseout="this.style.background='linear-gradient(135deg, #3498DB, #2980B9)'; this.style.transform='translateY(0px)'; this.style.boxShadow='0 5px 15px rgba(52,152,219,0.2)'">
        Войти для заказа
    </a>
<?php endif; ?>

                </div>

                <div style="background: rgba(255,255,255,0.95); border: 4px solid #F1DAAE; border-radius: 25px; padding: 35px 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); height: 480px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #F1DAAE, #D4A574, #F1DAAE); opacity: 0; transition: opacity 0.4s;"></div>
                    <div style="width: 150px; height: 150px; margin: 0 auto 25px; border-radius: 50%; overflow: hidden; border: 6px solid white; box-shadow: 0 15px 40px rgba(139,69,19,0.4);">
                        <img src="img/latte.jpg" style="width: 100%; height: 100%; object-fit: cover;"
                            onerror="this.parentElement.innerHTML='<div style=\'background: linear-gradient(135deg, #8B4513, #D2691E); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 55px; color: white;\'></div>'"
                            alt="Латте">
                    </div>
                    <h5 style="font-size: 32px; color: #492F1E; margin-bottom: 10px; font-family: Unbounded, cursive;">Латте</h5>
                    <p style="color: #6B4E31; margin-bottom: 15px; font-size: 16px; line-height: 1.6; min-height: 60px; display: flex; align-items: center; justify-content: center;">Нежная молочная пенка на сливочных сливках</p>
                    <div style="font-size: 28px; font-weight: 800; color: #492F1E; margin-bottom: 20px; background: #8A5E2D; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">350 ₽</div>
                    <?php if (isset($_SESSION['user'])): ?>
                    <form method="POST">
                        <input type="hidden" name="add_to_cart" value="2"> 
                        <button type="submit" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #F1DAAE, #E8C59A); color: #492F1E; border: 3px solid #D4A574; border-radius: 40px; font-weight: 700; font-size: 18px; cursor: pointer; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #492F1E, #6B4E31)'; this.style.color='#F1DAAE'; this.style.borderColor='#F1DAAE'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(73,47,30,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #F1DAAE, #E8C59A)'; this.style.color='#492F1E'; this.style.borderColor='#D4A574'; this.style.transform='none'; this.style.boxShadow='none'">В корзину</button>
                    </form>
                    <?php else: ?>
                        <a href="login.php" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #3498DB, #2980B9); color: white; text-decoration: none; border-radius: 40px; font-weight: 700; font-size: 18px; display: flex; align-items: center; justify-content: center; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #2980B9, #1F618D)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(52,152,219,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #3498DB, #2980B9)'; this.style.transform='none'; this.style.boxShadow='none'">
                            Войти для заказа
                        </a>
                    <?php endif; ?>
                </div>

                <div style="background: rgba(255,255,255,0.95); border: 4px solid #F1DAAE; border-radius: 25px; padding: 35px 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); height: 480px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #F1DAAE, #D4A574, #F1DAAE); opacity: 0; transition: opacity 0.4s;"></div>
                    <div style="width: 150px; height: 150px; margin: 0 auto 25px; border-radius: 50%; overflow: hidden; border: 6px solid white; box-shadow: 0 15px 40px rgba(139,69,19,0.4);">
                        <img src="img/capuccino.jpg" style="width: 100%; height: 100%; object-fit: cover;"
                            onerror="this.parentElement.innerHTML='<div style=\'background: linear-gradient(135deg, #8B4513, #D2691E); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 55px; color: white;\'>☕</div>'"
                            alt="Капучино">
                    </div>
                    <h5 style="font-size: 32px; color: #492F1E; margin-bottom: 10px; font-family: Unbounded, cursive;">Капучино</h5>
                    <p style="color: #6B4E31; margin-bottom: 15px; font-size: 16px; line-height: 1.6; min-height: 60px; display: flex; align-items: center; justify-content: center;">Итальянская классика пропорции 1:1:1</p>
                    <div style="font-size: 28px; font-weight: 800; color: #492F1E; margin-bottom: 20px; background: #8A5E2D; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">320 ₽</div>
                    <?php if (isset($_SESSION['user'])): ?>
                       <form method="POST">
                        <input type="hidden" name="add_to_cart" value="3"> 
                        <button type="submit" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #F1DAAE, #E8C59A); color: #492F1E; border: 3px solid #D4A574; border-radius: 40px; font-weight: 700; font-size: 18px; cursor: pointer; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #492F1E, #6B4E31)'; this.style.color='#F1DAAE'; this.style.borderColor='#F1DAAE'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(73,47,30,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #F1DAAE, #E8C59A)'; this.style.color='#492F1E'; this.style.borderColor='#D4A574'; this.style.transform='none'; this.style.boxShadow='none'">В корзину</button>
                    </form>
                    <?php else: ?>
                        <a href="login.php" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #3498DB, #2980B9); color: white; text-decoration: none; border-radius: 40px; font-weight: 700; font-size: 18px; display: flex; align-items: center; justify-content: center; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #2980B9, #1F618D)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(52,152,219,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #3498DB, #2980B9)'; this.style.transform='none'; this.style.boxShadow='none'">
                            Войти для заказа
                        </a>
                    <?php endif; ?>
                </div>

                <div style="background: rgba(255,255,255,0.95); border: 4px solid #F1DAAE; border-radius: 25px; padding: 35px 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); height: 480px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #F1DAAE, #D4A574, #F1DAAE); opacity: 0; transition: opacity 0.4s;"></div>
                    <div style="width: 150px; height: 150px; margin: 0 auto 25px; border-radius: 50%; overflow: hidden; border: 6px solid white; box-shadow: 0 15px 40px rgba(139,69,19,0.4);">
                        <img src="img/raf.jpg" style="width: 100%; height: 100%; object-fit: cover;"
                            onerror="this.parentElement.innerHTML='<div style=\'background: linear-gradient(135deg, #8B4513, #D2691E); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 55px; color: white;\'>☕</div>'"
                            alt="Раф">
                    </div>
                    <h5 style="font-size: 32px; color: #492F1E; margin-bottom: 10px; font-family: Unbounded, cursive;">Раф</h5>
                    <p style="color: #6B4E31; margin-bottom: 15px; font-size: 16px; line-height: 1.6; min-height: 60px; display: flex; align-items: center; justify-content: center;">	Велюровый вкус с ванильными сливками</p>
                    <div style="font-size: 28px; font-weight: 800; color: #492F1E; margin-bottom: 20px; background: #8A5E2D; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">380 ₽</div>
                    <?php if (isset($_SESSION['user'])): ?>
                        <form method="POST">
                        <input type="hidden" name="add_to_cart" value="4"> 
                        <button type="submit" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #F1DAAE, #E8C59A); color: #492F1E; border: 3px solid #D4A574; border-radius: 40px; font-weight: 700; font-size: 18px; cursor: pointer; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #492F1E, #6B4E31)'; this.style.color='#F1DAAE'; this.style.borderColor='#F1DAAE'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(73,47,30,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #F1DAAE, #E8C59A)'; this.style.color='#492F1E'; this.style.borderColor='#D4A574'; this.style.transform='none'; this.style.boxShadow='none'">В корзину</button>
                    </form>
                    <?php else: ?>
                        <a href="login.php" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #3498DB, #2980B9); color: white; text-decoration: none; border-radius: 40px; font-weight: 700; font-size: 18px; display: flex; align-items: center; justify-content: center; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #2980B9, #1F618D)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(52,152,219,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #3498DB, #2980B9)'; this.style.transform='none'; this.style.boxShadow='none'">
                            Войти для заказа
                        </a>
                    <?php endif; ?>
                </div>

                <div style="background: rgba(255,255,255,0.95); border: 4px solid #F1DAAE; border-radius: 25px; padding: 35px 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); height: 480px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #F1DAAE, #D4A574, #F1DAAE); opacity: 0; transition: opacity 0.4s;"></div>
                    <div style="width: 150px; height: 150px; margin: 0 auto 25px; border-radius: 50%; overflow: hidden; border: 6px solid white; box-shadow: 0 15px 40px rgba(139,69,19,0.4);">
                        <img src="img/espresso.jpg" style="width: 100%; height: 100%; object-fit: cover;"
                            onerror="this.parentElement.innerHTML='<div style=\'background: linear-gradient(135deg, #8B4513, #D2691E); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 55px; color: white;\'>☕</div>'"
                            alt="Эспрессо">
                    </div>
                    <h5 style="font-size: 32px; color: #492F1E; margin-bottom: 10px; font-family: Unbounded, cursive;">Эспрессо</h5>
                    <p style="color: #6B4E31; margin-bottom: 15px; font-size: 16px; line-height: 1.6; min-height: 60px; display: flex; align-items: center; justify-content: center;">Сила и аромат в маленькой чашке</p>
                    <div style="font-size: 28px; font-weight: 800; color: #492F1E; margin-bottom: 20px; background: #8A5E2D; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">200 ₽</div>
                    <?php if (isset($_SESSION['user'])): ?>
                        <form method="POST">
                        <input type="hidden" name="add_to_cart" value="5"> 
                        <button type="submit" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #F1DAAE, #E8C59A); color: #492F1E; border: 3px solid #D4A574; border-radius: 40px; font-weight: 700; font-size: 18px; cursor: pointer; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #492F1E, #6B4E31)'; this.style.color='#F1DAAE'; this.style.borderColor='#F1DAAE'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(73,47,30,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #F1DAAE, #E8C59A)'; this.style.color='#492F1E'; this.style.borderColor='#D4A574'; this.style.transform='none'; this.style.boxShadow='none'">В корзину</button>
                    </form>
                    <?php else: ?>
                        <a href="login.php" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #3498DB, #2980B9); color: white; text-decoration: none; border-radius: 40px; font-weight: 700; font-size: 18px; display: flex; align-items: center; justify-content: center; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #2980B9, #1F618D)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(52,152,219,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #3498DB, #2980B9)'; this.style.transform='none'; this.style.boxShadow='none'">
                            Войти для заказа
                        </a>
                    <?php endif; ?>
                </div>

                <div style="background: rgba(255,255,255,0.95); border: 4px solid #F1DAAE; border-radius: 25px; padding: 35px 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); height: 480px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #F1DAAE, #D4A574, #F1DAAE); opacity: 0; transition: opacity 0.4s;"></div>
                    <div style="width: 150px; height: 150px; margin: 0 auto 25px; border-radius: 50%; overflow: hidden; border: 6px solid white; box-shadow: 0 15px 40px rgba(139,69,19,0.4);">
                        <img src="img/flat_white.jpg" style="width: 100%; height: 100%; object-fit: cover;"
                            onerror="this.parentElement.innerHTML='<div style=\'background: linear-gradient(135deg, #8B4513, #D2691E); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 55px; color: white;\'>☕</div>'"
                            alt="Флэт Уайт">
                    </div>
                    <h5 style="font-size: 32px; color: #492F1E; margin-bottom: 10px; font-family: Unbounded, cursive;">Флэт Уайт</h5>
                    <p style="color: #6B4E31; margin-bottom: 15px; font-size: 16px; line-height: 1.6; min-height: 60px; display: flex; align-items: center; justify-content: center;">	Двойной эспрессо с бархатным молоком</p>
                    <div style="font-size: 28px; font-weight: 800; color: #492F1E; margin-bottom: 20px; background: #8A5E2D; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">340 ₽</div>
                    <?php if (isset($_SESSION['user'])): ?>
                        <form method="POST">
                        <input type="hidden" name="add_to_cart" value="6"> 
                        <button type="submit" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #F1DAAE, #E8C59A); color: #492F1E; border: 3px solid #D4A574; border-radius: 40px; font-weight: 700; font-size: 18px; cursor: pointer; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #492F1E, #6B4E31)'; this.style.color='#F1DAAE'; this.style.borderColor='#F1DAAE'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(73,47,30,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #F1DAAE, #E8C59A)'; this.style.color='#492F1E'; this.style.borderColor='#D4A574'; this.style.transform='none'; this.style.boxShadow='none'">В корзину</button>
                    </form>
                    <?php else: ?>
                        <a href="login.php" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #3498DB, #2980B9); color: white; text-decoration: none; border-radius: 40px; font-weight: 700; font-size: 18px; display: flex; align-items: center; justify-content: center; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #2980B9, #1F618D)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(52,152,219,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #3498DB, #2980B9)'; this.style.transform='none'; this.style.boxShadow='none'">
                            Войти для заказа
                        </a>
                    <?php endif; ?>
                </div>

                <div style="background: rgba(255,255,255,0.95); border: 4px solid #F1DAAE; border-radius: 25px; padding: 35px 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); height: 480px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #F1DAAE, #D4A574, #F1DAAE); opacity: 0; transition: opacity 0.4s;"></div>
                    <div style="width: 150px; height: 150px; margin: 0 auto 25px; border-radius: 50%; overflow: hidden; border: 6px solid white; box-shadow: 0 15px 40px rgba(139,69,19,0.4);">
                        <img src="img/matcha_latte.jpg" style="width: 100%; height: 100%; object-fit: cover;"
                            onerror="this.parentElement.innerHTML='<div style=\'background: linear-gradient(135deg, #8B4513, #D2691E); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 55px; color: white;\'>☕</div>'"
                            alt="Матча латте">
                    </div>
                    <h5 style="font-size: 32px; color: #492F1E; margin-bottom: 10px; font-family: Unbounded, cursive;">Матча латте</h5>
                    <p style="color: #6B4E31; margin-bottom: 15px; font-size: 16px; line-height: 1.6; min-height: 60px; display: flex; align-items: center; justify-content: center;">Японский ритуал в чашке</p>
                    <div style="font-size: 28px; font-weight: 800; color: #492F1E; margin-bottom: 20px; background: #8A5E2D; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">390 ₽</div>
                    <?php if (isset($_SESSION['user'])): ?>
                        <form method="POST">
                        <input type="hidden" name="add_to_cart" value="7"> 
                        <button type="submit" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #F1DAAE, #E8C59A); color: #492F1E; border: 3px solid #D4A574; border-radius: 40px; font-weight: 700; font-size: 18px; cursor: pointer; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #492F1E, #6B4E31)'; this.style.color='#F1DAAE'; this.style.borderColor='#F1DAAE'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(73,47,30,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #F1DAAE, #E8C59A)'; this.style.color='#492F1E'; this.style.borderColor='#D4A574'; this.style.transform='none'; this.style.boxShadow='none'">В корзину</button>
                    </form>
                    <?php else: ?>
                        <a href="login.php" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #3498DB, #2980B9); color: white; text-decoration: none; border-radius: 40px; font-weight: 700; font-size: 18px; display: flex; align-items: center; justify-content: center; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #2980B9, #1F618D)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(52,152,219,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #3498DB, #2980B9)'; this.style.transform='none'; this.style.boxShadow='none'">
                            Войти для заказа
                        </a>
                    <?php endif; ?>
                </div>

                <div style="background: rgba(255,255,255,0.95); border: 4px solid #F1DAAE; border-radius: 25px; padding: 35px 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); height: 480px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #F1DAAE, #D4A574, #F1DAAE); opacity: 0; transition: opacity 0.4s;"></div>
                    <div style="width: 150px; height: 150px; margin: 0 auto 25px; border-radius: 50%; overflow: hidden; border: 6px solid white; box-shadow: 0 15px 40px rgba(139,69,19,0.4);">
                        <img src="img/greeen_tea.jpg" style="width: 100%; height: 100%; object-fit: cover;"
                            onerror="this.parentElement.innerHTML='<div style=\'background: linear-gradient(135deg, #8B4513, #D2691E); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 55px; color: white;\'>☕</div>'"
                            alt="Зеленый чай">
                    </div>
                    <h5 style="font-size: 32px; color: #492F1E; margin-bottom: 10px; font-family: Unbounded, cursive;">Зеленый чай</h5>
                    <p style="color: #6B4E31; margin-bottom: 15px; font-size: 16px; line-height: 1.6; min-height: 60px; display: flex; align-items: center; justify-content: center;">Сенча с цветами вишни</p>
                    <div style="font-size: 28px; font-weight: 800; color: #492F1E; margin-bottom: 20px; background: #8A5E2D; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">220 ₽</div>
                    <?php if (isset($_SESSION['user'])): ?>
                       <form method="POST">
                        <input type="hidden" name="add_to_cart" value="8"> 
                        <button type="submit" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #F1DAAE, #E8C59A); color: #492F1E; border: 3px solid #D4A574; border-radius: 40px; font-weight: 700; font-size: 18px; cursor: pointer; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #492F1E, #6B4E31)'; this.style.color='#F1DAAE'; this.style.borderColor='#F1DAAE'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(73,47,30,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #F1DAAE, #E8C59A)'; this.style.color='#492F1E'; this.style.borderColor='#D4A574'; this.style.transform='none'; this.style.boxShadow='none'">В корзину</button>
                    </form>
                    <?php else: ?>
                        <a href="login.php" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #3498DB, #2980B9); color: white; text-decoration: none; border-radius: 40px; font-weight: 700; font-size: 18px; display: flex; align-items: center; justify-content: center; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #2980B9, #1F618D)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(52,152,219,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #3498DB, #2980B9)'; this.style.transform='none'; this.style.boxShadow='none'">
                            Войти для заказа
                        </a>
                    <?php endif; ?>
                </div>


                <div style="background: rgba(255,255,255,0.95); border: 4px solid #F1DAAE; border-radius: 25px; padding: 35px 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); height: 480px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #F1DAAE, #D4A574, #F1DAAE); opacity: 0; transition: opacity 0.4s;"></div>
                    <div style="width: 150px; height: 150px; margin: 0 auto 25px; border-radius: 50%; overflow: hidden; border: 6px solid white; box-shadow: 0 15px 40px rgba(139,69,19,0.4);">
                        <img src="img/cheesecake.jpg" style="width: 100%; height: 100%; object-fit: cover;"
                            onerror="this.parentElement.innerHTML='<div style=\'background: linear-gradient(135deg, #8B4513, #D2691E); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 55px; color: white;\'>☕</div>'"
                            alt="Чизкейк">
                    </div>
                    <h5 style="font-size: 32px; color: #492F1E; margin-bottom: 10px; font-family: Unbounded, cursive;">Чизкейк</h5>
                    <p style="color: #6B4E31; margin-bottom: 15px; font-size: 16px; line-height: 1.6; min-height: 60px; display: flex; align-items: center; justify-content: center;">Нью-йоркский с ванилью</p>
                    <div style="font-size: 28px; font-weight: 800; color: #492F1E; margin-bottom: 20px; background: #8A5E2D; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">290 ₽</div>
                    <?php if (isset($_SESSION['user'])): ?>
                        <form method="POST">
                        <input type="hidden" name="add_to_cart" value="9"> 
                        <button type="submit" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #F1DAAE, #E8C59A); color: #492F1E; border: 3px solid #D4A574; border-radius: 40px; font-weight: 700; font-size: 18px; cursor: pointer; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #492F1E, #6B4E31)'; this.style.color='#F1DAAE'; this.style.borderColor='#F1DAAE'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(73,47,30,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #F1DAAE, #E8C59A)'; this.style.color='#492F1E'; this.style.borderColor='#D4A574'; this.style.transform='none'; this.style.boxShadow='none'">В корзину</button>
                    </form>
                    <?php else: ?>
                        <a href="login.php" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #3498DB, #2980B9); color: white; text-decoration: none; border-radius: 40px; font-weight: 700; font-size: 18px; display: flex; align-items: center; justify-content: center; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #2980B9, #1F618D)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(52,152,219,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #3498DB, #2980B9)'; this.style.transform='none'; this.style.boxShadow='none'">
                            Войти для заказа
                        </a>
                    <?php endif; ?>
                </div>

                <div style="background: rgba(255,255,255,0.95); border: 4px solid #F1DAAE; border-radius: 25px; padding: 35px 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); height: 480px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #F1DAAE, #D4A574, #F1DAAE); opacity: 0; transition: opacity 0.4s;"></div>
                    <div style="width: 150px; height: 150px; margin: 0 auto 25px; border-radius: 50%; overflow: hidden; border: 6px solid white; box-shadow: 0 15px 40px rgba(139,69,19,0.4);">
                        <img src="img/tiramisu.jpg" style="width: 100%; height: 100%; object-fit: cover;"
                            onerror="this.parentElement.innerHTML='<div style=\'background: linear-gradient(135deg, #8B4513, #D2691E); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 55px; color: white;\'>☕</div>'"
                            alt="Тирамису">
                    </div>
                    <h5 style="font-size: 32px; color: #492F1E; margin-bottom: 10px; font-family: Unbounded, cursive;">Тирамису</h5>
                    <p style="color: #6B4E31; margin-bottom: 15px; font-size: 16px; line-height: 1.6; min-height: 60px; display: flex; align-items: center; justify-content: center;">Итальянский десерт с маскарпоне</p>
                    <div style="font-size: 28px; font-weight: 800; color: #492F1E; margin-bottom: 20px; background: #8A5E2D; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">310 ₽</div>
                    <?php if (isset($_SESSION['user'])): ?>
                        <form method="POST">
                        <input type="hidden" name="add_to_cart" value="10"> 
                        <button type="submit" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #F1DAAE, #E8C59A); color: #492F1E; border: 3px solid #D4A574; border-radius: 40px; font-weight: 700; font-size: 18px; cursor: pointer; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #492F1E, #6B4E31)'; this.style.color='#F1DAAE'; this.style.borderColor='#F1DAAE'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(73,47,30,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #F1DAAE, #E8C59A)'; this.style.color='#492F1E'; this.style.borderColor='#D4A574'; this.style.transform='none'; this.style.boxShadow='none'">В корзину</button>
                    </form>
                    <?php else: ?>
                        <a href="login.php" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #3498DB, #2980B9); color: white; text-decoration: none; border-radius: 40px; font-weight: 700; font-size: 18px; display: flex; align-items: center; justify-content: center; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #2980B9, #1F618D)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(52,152,219,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #3498DB, #2980B9)'; this.style.transform='none'; this.style.boxShadow='none'">
                            Войти для заказа
                        </a>
                    <?php endif; ?>
                </div>

                <div style="background: rgba(255,255,255,0.95); border: 4px solid #F1DAAE; border-radius: 25px; padding: 35px 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); height: 480px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #F1DAAE, #D4A574, #F1DAAE); opacity: 0; transition: opacity 0.4s;"></div>
                    <div style="width: 150px; height: 150px; margin: 0 auto 25px; border-radius: 50%; overflow: hidden; border: 6px solid white; box-shadow: 0 15px 40px rgba(139,69,19,0.4);">
                        <img src="img/ovsyanka.jpg" style="width: 100%; height: 100%; object-fit: cover;"
                            onerror="this.parentElement.innerHTML='<div style=\'background: linear-gradient(135deg, #8B4513, #D2691E); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 55px; color: white;\'>☕</div>'"
                            alt="Овсянка">
                    </div>
                    <h5 style="font-size: 32px; color: #492F1E; margin-bottom: 10px; font-family: Unbounded, cursive;">Овсянка с ягодами</h5>
                    <p style="color: #6B4E31; margin-bottom: 15px; font-size: 16px; line-height: 1.6; min-height: 60px; display: flex; align-items: center; justify-content: center;">Овсяные хлопья на кокосовом молоке с ежевикой и малина</p>
                    <div style="font-size: 28px; font-weight: 800; color: #492F1E; margin-bottom: 20px; background: #8A5E2D; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">300 ₽</div>
                    <?php if (isset($_SESSION['user'])): ?>
                        <form method="POST">
                        <input type="hidden" name="add_to_cart" value="11"> 
                        <button type="submit" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #F1DAAE, #E8C59A); color: #492F1E; border: 3px solid #D4A574; border-radius: 40px; font-weight: 700; font-size: 18px; cursor: pointer; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #492F1E, #6B4E31)'; this.style.color='#F1DAAE'; this.style.borderColor='#F1DAAE'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(73,47,30,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #F1DAAE, #E8C59A)'; this.style.color='#492F1E'; this.style.borderColor='#D4A574'; this.style.transform='none'; this.style.boxShadow='none'">В корзину</button>
                    </form>
                    <?php else: ?>
                        <a href="login.php" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #3498DB, #2980B9); color: white; text-decoration: none; border-radius: 40px; font-weight: 700; font-size: 18px; display: flex; align-items: center; justify-content: center; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #2980B9, #1F618D)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(52,152,219,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #3498DB, #2980B9)'; this.style.transform='none'; this.style.boxShadow='none'">
                            Войти для заказа
                        </a>
                    <?php endif; ?>
                </div>

                <div style="background: rgba(255,255,255,0.95); border: 4px solid #F1DAAE; border-radius: 25px; padding: 35px 25px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3); height: 480px; display: flex; flex-direction: column; justify-content: space-between;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #F1DAAE, #D4A574, #F1DAAE); opacity: 0; transition: opacity 0.4s;"></div>
                    <div style="width: 150px; height: 150px; margin: 0 auto 25px; border-radius: 50%; overflow: hidden; border: 6px solid white; box-shadow: 0 15px 40px rgba(139,69,19,0.4);">
                        <img src="img/avocado.jpg" style="width: 100%; height: 100%; object-fit: cover;"
                            onerror="this.parentElement.innerHTML='<div style=\'background: linear-gradient(135deg, #8B4513, #D2691E); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 55px; color: white;\'>☕</div>'"
                            alt="Авокадо-тост">
                    </div>
                    <h5 style="font-size: 32px; color: #492F1E; margin-bottom: 10px; font-family: Unbounded, cursive;">Авокадо-тост</h5>
                    <p style="color: #6B4E31; margin-bottom: 15px; font-size: 16px; line-height: 1.6; min-height: 60px; display: flex; align-items: center; justify-content: center;">Агуакате, яичко и чиа на цельнозерновом хлебе</p>
                    <div style="font-size: 28px; font-weight: 800; color: #492F1E; margin-bottom: 20px; background: #8A5E2D; -webkit-background-clip: text; -webkit-text-fill-color: transparent;">320 ₽</div>
                    <?php if (isset($_SESSION['user'])): ?>
                        <form method="POST">
                        <input type="hidden" name="add_to_cart" value="12"> 
                        <button type="submit" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #F1DAAE, #E8C59A); color: #492F1E; border: 3px solid #D4A574; border-radius: 40px; font-weight: 700; font-size: 18px; cursor: pointer; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #492F1E, #6B4E31)'; this.style.color='#F1DAAE'; this.style.borderColor='#F1DAAE'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(73,47,30,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #F1DAAE, #E8C59A)'; this.style.color='#492F1E'; this.style.borderColor='#D4A574'; this.style.transform='none'; this.style.boxShadow='none'">В корзину</button>
                    </form>
                    <?php else: ?>
                        <a href="login.php" style="width: 100%; padding: 20px; background: linear-gradient(135deg, #3498DB, #2980B9); color: white; text-decoration: none; border-radius: 40px; font-weight: 700; font-size: 18px; display: flex; align-items: center; justify-content: center; font-family: Unbounded;"
                            onmouseover="this.style.background='linear-gradient(135deg, #2980B9, #1F618D)'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 35px rgba(52,152,219,0.4)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #3498DB, #2980B9)'; this.style.transform='none'; this.style.boxShadow='none'">
                            Войти для заказа
                        </a>
                    <?php endif; ?>
                </div>





            </div>
        </section>


        <section id="gallery" class="gallery">
            <p>Погрузитесь в атмосферу через наши фото. Здесь вы увидите<br>
                стильный интерьер с панорамными окнами, аппетитные чашки кофе, улыбки<br>
                гостей и наши фирменные десерты.</p>
            <div class="images">
                <img src="img/gallery.jpg" height="450px">
                <img src="img/gallery2.jpg" height="450px">
                <img src="img/gallery3.jpg" height="450px">
            </div>
        </section>

        <section id="contact" class="contact">
            <h4>Приходите к нам или звоните — мы всегда рады!</h4>
            <p>Часы работы: <br>
                Пн–Пт: 8:00–22:00,<br>
                Сб–Вс: 9:00–23:00.</p>
            <p>Телефон: +7 (800) 555-35-35.<br>
                Email: hello@coffeebreak.ru</p>
            <p>Адрес: Москва, ул. Ленина, 25 (м. Площадь Революции, 5 мин пешком).</p>
            <img src="img/map.jpg" width="1200px" height="" alt="Карта">
        </section>

        <section id="order">
            <div class="order">
                <p>Хотите забронировать столик или заказать<br>кофе с собой? Авторизуйтесь и скорее закажите<br>свежий кофе, вкусные десерты и хорошее настроение!</p>
                <button><a href="login.php">Заказать/Забронировать</a></button>
            </div>
        </section>


    </main>
    <footer>
        <h5>Coffee Break</h5>
        <p>📍 Москва, ул. Ленина, 25 (м. Площадь Революции)<br>
            ☎️ +7 (800) 555-35-35<br>
            ✉️hello@coffeebreak.ru</p>
        <div class="social">
            <a href=""><img src="img/free-icon-telegram-739260 (1).png" height="40px"></a>
            <a href=""><img src="img/free-icon-vk-16546797.png" height="50px"></a>
        </div>
        <p>© 2026 Coffee Break. Все права защищены.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.add-to-cart').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const formData = new FormData();
                    formData.append('action', 'add');
                    formData.append('menu_id', this.dataset.id);

                    fetch('account.php', {
                        method: 'POST',
                        body: formData
                    }).then(() => {
                        const original = this.innerHTML;
                        this.innerHTML = '✓ В корзине!';
                        this.style.background = 'linear-gradient(135deg, #27AE60, #2ECC71)';
                        setTimeout(() => {
                            this.innerHTML = original;
                            this.style.background = '';
                            location.reload();
                        }, 2000);
                    });
                });
            });
        });
    </script>





</body>

</html>
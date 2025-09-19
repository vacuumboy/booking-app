<?php
/**
 * Веб-интерфейс для очистки кеша Laravel
 * Доступ: http://ваш-сайт.com/clear-cache.php
 */

// Переходим в корень проекта
chdir(__DIR__ . '/../');

// Проверяем наличие artisan
if (!file_exists('artisan')) {
    die('Ошибка: Не найден файл artisan. Проверьте путь к проекту.');
}

// Простая защита (опционально)
$allowed_ips = ['127.0.0.1', '::1']; // Добавьте ваш IP
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Закомментируйте следующие строки для отключения защиты по IP
// if (!in_array($client_ip, $allowed_ips)) {
//     die('Доступ запрещен');
// }

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Очистка кеша Laravel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn.danger { background: #dc3545; }
        .btn.danger:hover { background: #c82333; }
        .btn.success { background: #28a745; }
        .btn.success:hover { background: #218838; }
        .output { background: #f8f9fa; padding: 15px; border-radius: 4px; margin: 10px 0; font-family: monospace; white-space: pre-wrap; border: 1px solid #e9ecef; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Очистка кеша Laravel</h1>
        
        <?php if (isset($_POST['action'])): ?>
            <div class="output">
                <?php
                $action = $_POST['action'];
                
                if ($action === 'clear') {
                    echo "<span class='info'>Начинаю очистку кеша...</span>\n\n";
                    
                    $commands = [
                        'config:clear' => 'Очистка кеша конфигурации',
                        'cache:clear' => 'Очистка кеша приложения',
                        'route:clear' => 'Очистка кеша маршрутов',
                        'view:clear' => 'Очистка скомпилированных представлений',
                    ];
                    
                    $all_success = true;
                    foreach ($commands as $cmd => $desc) {
                        echo "$desc\n";
                        $output = '';
                        $return_var = 0;
                        exec("php artisan $cmd 2>&1", $output, $return_var);
                        
                        if ($return_var === 0) {
                            echo "<span class='success'>✓ $cmd - выполнено успешно</span>\n";
                        } else {
                            echo "<span class='error'>✗ $cmd - ошибка</span>\n";
                            if (!empty($output)) {
                                echo "<span class='error'>Вывод: " . implode("\n", $output) . "</span>\n";
                            }
                            $all_success = false;
                        }
                        echo "\n";
                    }
                    
                    if ($all_success) {
                        echo "<span class='success'>🎉 Кеш успешно очищен!</span>\n";
                    } else {
                        echo "<span class='error'>⚠️ Есть ошибки при очистке кеша.</span>\n";
                    }
                    
                } elseif ($action === 'optimize') {
                    echo "<span class='info'>Оптимизация для продакшена...</span>\n\n";
                    
                    $commands = [
                        'config:cache' => 'Создание кеша конфигурации',
                        'route:cache' => 'Создание кеша маршрутов',
                        'view:cache' => 'Предварительная компиляция представлений',
                    ];
                    
                    $all_success = true;
                    foreach ($commands as $cmd => $desc) {
                        echo "$desc\n";
                        $output = '';
                        $return_var = 0;
                        exec("php artisan $cmd 2>&1", $output, $return_var);
                        
                        if ($return_var === 0) {
                            echo "<span class='success'>✓ $cmd - выполнено успешно</span>\n";
                        } else {
                            echo "<span class='error'>✗ $cmd - ошибка</span>\n";
                            if (!empty($output)) {
                                echo "<span class='error'>Вывод: " . implode("\n", $output) . "</span>\n";
                            }
                            $all_success = false;
                        }
                        echo "\n";
                    }
                    
                    if ($all_success) {
                        echo "<span class='success'>🚀 Оптимизация завершена!</span>\n";
                    } else {
                        echo "<span class='error'>⚠️ Есть ошибки при оптимизации.</span>\n";
                    }
                    
                } elseif ($action === 'full') {
                    echo "<span class='info'>Полная очистка и оптимизация...</span>\n\n";
                    
                    // Сначала очищаем
                    $clear_commands = [
                        'config:clear' => 'Очистка кеша конфигурации',
                        'cache:clear' => 'Очистка кеша приложения',
                        'route:clear' => 'Очистка кеша маршрутов',
                        'view:clear' => 'Очистка скомпилированных представлений',
                    ];
                    
                    foreach ($clear_commands as $cmd => $desc) {
                        echo "$desc\n";
                        exec("php artisan $cmd 2>&1", $output, $return_var);
                        if ($return_var === 0) {
                            echo "<span class='success'>✓ $cmd - выполнено</span>\n";
                        } else {
                            echo "<span class='error'>✗ $cmd - ошибка</span>\n";
                        }
                        echo "\n";
                    }
                    
                    echo "<span class='info'>Создание оптимизированного кеша...</span>\n\n";
                    
                    // Потом оптимизируем
                    $optimize_commands = [
                        'config:cache' => 'Создание кеша конфигурации',
                    ];
                    
                    foreach ($optimize_commands as $cmd => $desc) {
                        echo "$desc\n";
                        exec("php artisan $cmd 2>&1", $output, $return_var);
                        if ($return_var === 0) {
                            echo "<span class='success'>✓ $cmd - выполнено</span>\n";
                        } else {
                            echo "<span class='error'>✗ $cmd - ошибка</span>\n";
                        }
                        echo "\n";
                    }
                    
                    echo "<span class='success'>🎯 Полная очистка и оптимизация завершена!</span>\n";
                }
                
                echo "\n<span class='info'>Время выполнения: " . date('Y-m-d H:i:s') . "</span>\n";
                ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <h3>Выберите действие:</h3>
            
            <button type="submit" name="action" value="clear" class="btn">
                🧹 Очистить кеш
            </button>
            
            <button type="submit" name="action" value="optimize" class="btn success">
                🚀 Оптимизировать для продакшена
            </button>
            
            <button type="submit" name="action" value="full" class="btn danger">
                🎯 Полная очистка + оптимизация
            </button>
        </form>
        
        <hr>
        
        <h3>📖 Описание действий:</h3>
        <ul>
            <li><strong>Очистить кеш:</strong> Очищает все типы кеша Laravel (config, cache, route, view)</li>
            <li><strong>Оптимизировать:</strong> Создает оптимизированный кеш для продакшена</li>
            <li><strong>Полная очистка:</strong> Сначала очищает, затем создает оптимизированный кеш</li>
        </ul>
        
        <div class="output">
            <strong>Когда использовать:</strong>
            - После изменений в .env файле
            - После обновления кода
            - При проблемах с кешем
            - После загрузки на хостинг
        </div>
        
        <p><small>⚠️ <strong>Безопасность:</strong> Удалите этот файл после использования на продакшн сервере!</small></p>
    </div>
</body>
</html> 
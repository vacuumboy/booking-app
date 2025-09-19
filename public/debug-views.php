<?php
/**
 * Диагностический скрипт для проблем с представлениями Laravel
 * Доступ: http://ваш-сайт.com/debug-views.php
 */

// Переходим в корень проекта
chdir(__DIR__ . '/../');

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Диагностика представлений Laravel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 4px; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .btn { background: #007bff; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; margin: 5px 0; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Диагностика представлений Laravel</h1>
        
        <?php
        
        // Проверка 1: Существование artisan
        echo "<div class='section'>";
        echo "<h3>1. Проверка файла artisan</h3>";
        if (file_exists('artisan')) {
            echo "<div class='success'>✓ Файл artisan найден</div>";
        } else {
            echo "<div class='error'>✗ Файл artisan НЕ найден! Проверьте путь к проекту.</div>";
        }
        echo "</div>";
        
        // Проверка 2: Структура папок
        echo "<div class='section'>";
        echo "<h3>2. Структура папок</h3>";
        $paths = [
            'resources/views' => 'Папка представлений',
            'resources/views/welcome.blade.php' => 'Файл welcome.blade.php',
            'storage/framework/views' => 'Папка скомпилированных представлений',
            'storage/app' => 'Папка storage/app',
            'storage/logs' => 'Папка логов',
            'bootstrap/cache' => 'Папка bootstrap/cache',
        ];
        
        foreach ($paths as $path => $desc) {
            if (file_exists($path)) {
                echo "<div class='success'>✓ $desc: $path</div>";
            } else {
                echo "<div class='error'>✗ $desc НЕ найден: $path</div>";
            }
        }
        echo "</div>";
        
        // Проверка 3: Права доступа
        echo "<div class='section'>";
        echo "<h3>3. Права доступа</h3>";
        $writablePaths = [
            'storage',
            'storage/app',
            'storage/framework',
            'storage/framework/views',
            'storage/logs',
            'bootstrap/cache',
        ];
        
        foreach ($writablePaths as $path) {
            if (file_exists($path)) {
                if (is_writable($path)) {
                    echo "<div class='success'>✓ $path - доступен для записи</div>";
                } else {
                    echo "<div class='error'>✗ $path - НЕ доступен для записи (chmod 755 или 775)</div>";
                }
            } else {
                echo "<div class='warning'>⚠ $path - не существует</div>";
            }
        }
        echo "</div>";
        
        // Проверка 4: Содержимое welcome.blade.php
        echo "<div class='section'>";
        echo "<h3>4. Содержимое welcome.blade.php</h3>";
        $welcomePath = 'resources/views/welcome.blade.php';
        if (file_exists($welcomePath)) {
            $content = file_get_contents($welcomePath);
            $lines = substr_count($content, "\n") + 1;
            $size = filesize($welcomePath);
            echo "<div class='success'>✓ Файл найден</div>";
            echo "<div class='info'>📄 Размер: $size байт, строк: $lines</div>";
            echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "...</pre>";
        } else {
            echo "<div class='error'>✗ Файл welcome.blade.php не найден!</div>";
        }
        echo "</div>";
        
        // Проверка 5: Конфигурация
        echo "<div class='section'>";
        echo "<h3>5. Информация о среде</h3>";
        echo "<div class='info'>PHP версия: " . phpversion() . "</div>";
        echo "<div class='info'>Текущая директория: " . getcwd() . "</div>";
        echo "<div class='info'>Документ рут: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'не определен') . "</div>";
        echo "<div class='info'>Скрипт: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'не определен') . "</div>";
        echo "</div>";
        
        // Проверка 6: Laravel команды
        echo "<div class='section'>";
        echo "<h3>6. Тест Laravel команд</h3>";
        
        if (file_exists('artisan')) {
            $commands = [
                'route:list --name=welcome' => 'Проверка маршрута welcome',
                'view:clear' => 'Очистка представлений',
                'config:show view' => 'Конфигурация представлений',
            ];
            
            foreach ($commands as $cmd => $desc) {
                echo "<h4>$desc</h4>";
                $output = [];
                $return_var = 0;
                exec("php artisan $cmd 2>&1", $output, $return_var);
                
                if ($return_var === 0) {
                    echo "<div class='success'>✓ Команда выполнена успешно</div>";
                    if (!empty($output)) {
                        echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
                    }
                } else {
                    echo "<div class='error'>✗ Ошибка выполнения команды</div>";
                    if (!empty($output)) {
                        echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
                    }
                }
            }
        }
        echo "</div>";
        
        // Проверка 7: Логи ошибок
        echo "<div class='section'>";
        echo "<h3>7. Последние ошибки из логов</h3>";
        $logPath = 'storage/logs/laravel.log';
        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            $lines = explode("\n", $logContent);
            $recentLines = array_slice($lines, -20); // Последние 20 строк
            
            echo "<div class='info'>Показаны последние 20 строк из лога:</div>";
            echo "<pre style='max-height: 300px; overflow-y: scroll;'>" . htmlspecialchars(implode("\n", $recentLines)) . "</pre>";
        } else {
            echo "<div class='warning'>⚠ Файл логов не найден: $logPath</div>";
        }
        echo "</div>";
        
        // Проверка 8: Быстрое исправление
        echo "<div class='section'>";
        echo "<h3>8. Быстрое исправление</h3>";
        echo "<p>Если проблема в кэше, попробуйте:</p>";
        echo "<form method='post' style='display: inline;'>";
        echo "<button type='submit' name='fix' value='clear' class='btn'>Очистить весь кэш</button>";
        echo "</form>";
        
        if (isset($_POST['fix']) && $_POST['fix'] === 'clear') {
            echo "<div style='margin-top: 15px;'>";
            echo "<h4>Выполняю очистку кэша...</h4>";
            
            $clearCommands = [
                'config:clear',
                'cache:clear',
                'route:clear',
                'view:clear',
            ];
            
            foreach ($clearCommands as $cmd) {
                $output = [];
                $return_var = 0;
                exec("php artisan $cmd 2>&1", $output, $return_var);
                
                if ($return_var === 0) {
                    echo "<div class='success'>✓ $cmd - выполнено</div>";
                } else {
                    echo "<div class='error'>✗ $cmd - ошибка</div>";
                    if (!empty($output)) {
                        echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
                    }
                }
            }
            
            echo "<div class='info'>Попробуйте обновить главную страницу сайта.</div>";
            echo "</div>";
        }
        echo "</div>";
        
        ?>
        
        <div class="section info">
            <h3>💡 Рекомендации</h3>
            <ul>
                <li>Если файл welcome.blade.php существует, но ошибка остается - очистите кэш</li>
                <li>Проверьте права доступа к папкам storage и bootstrap/cache</li>
                <li>Убедитесь, что .env файл настроен правильно</li>
                <li>Проверьте логи на наличие более подробных ошибок</li>
                <li>После исправления удалите этот диагностический файл</li>
            </ul>
        </div>
        
        <p><small>⚠️ <strong>Безопасность:</strong> Удалите этот файл после диагностики!</small></p>
    </div>
</body>
</html>


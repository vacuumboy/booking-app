<?php
/**
 * Автоматическая очистка кеша Laravel
 * Используйте этот файл для быстрой очистки всех типов кеша
 * 
 * Использование:
 * php clear_cache.php
 * или через браузер: http://yoursite.com/clear_cache.php
 */

// Проверяем, что мы находимся в корне Laravel приложения
if (!file_exists('artisan')) {
    die('Ошибка: Файл artisan не найден. Убедитесь, что скрипт находится в корне Laravel приложения.');
}

echo "=== Очистка кеша Laravel ===\n";
echo "Начинаю очистку...\n\n";

// Функция для выполнения команд artisan
function runArtisanCommand($command) {
    $fullCommand = "php artisan $command";
    echo "Выполняю: $fullCommand\n";
    
    ob_start();
    system($fullCommand . ' 2>&1', $return_var);
    $output = ob_get_clean();
    
    if ($return_var === 0) {
        echo "✓ Успешно: $command\n";
    } else {
        echo "✗ Ошибка при выполнении: $command\n";
        echo "Вывод: $output\n";
    }
    echo "\n";
    
    return $return_var === 0;
}

// Список команд для очистки
$clearCommands = [
    'config:clear'    => 'Очистка кеша конфигурации',
    'cache:clear'     => 'Очистка кеша приложения',
    'route:clear'     => 'Очистка кеша маршрутов',
    'view:clear'      => 'Очистка скомпилированных представлений',
    'event:clear'     => 'Очистка кеша событий',
    'queue:clear'     => 'Очистка неудачных заданий очереди',
];

// Выполняем команды очистки
$success = true;
foreach ($clearCommands as $command => $description) {
    echo "$description\n";
    if (!runArtisanCommand($command)) {
        $success = false;
    }
}

// Дополнительная очистка файлов кеша
echo "Дополнительная очистка файлов кеша...\n";

// Очистка кеша сессий (если используется файловый драйвер)
if (is_dir('storage/framework/sessions')) {
    $sessionFiles = glob('storage/framework/sessions/*');
    if ($sessionFiles) {
        foreach ($sessionFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "✓ Очищены файлы сессий\n";
    }
}

// Очистка кеша представлений
if (is_dir('storage/framework/views')) {
    $viewFiles = glob('storage/framework/views/*');
    if ($viewFiles) {
        foreach ($viewFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "✓ Очищены файлы представлений\n";
    }
}

// Очистка кеша приложения
if (is_dir('storage/framework/cache/data')) {
    $cacheFiles = glob('storage/framework/cache/data/*');
    if ($cacheFiles) {
        foreach ($cacheFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "✓ Очищены файлы кеша данных\n";
    }
}

echo "\n";

// Опционально: создание оптимизированного кеша для продакшена
if (isset($_GET['optimize']) || (isset($argv[1]) && $argv[1] === 'optimize')) {
    echo "=== Оптимизация для продакшена ===\n";
    
    $optimizeCommands = [
        'config:cache'  => 'Создание кеша конфигурации',
        'route:cache'   => 'Создание кеша маршрутов',
        'view:cache'    => 'Предварительная компиляция представлений',
    ];
    
    foreach ($optimizeCommands as $command => $description) {
        echo "$description\n";
        runArtisanCommand($command);
    }
}

// Финальное сообщение
echo "=== Результат ===\n";
if ($success) {
    echo "✓ Все операции очистки выполнены успешно!\n";
    echo "Ваше приложение готово к работе.\n";
} else {
    echo "✗ Некоторые операции завершились с ошибками.\n";
    echo "Проверьте права доступа к файлам и папкам.\n";
}

echo "\n";
echo "Время выполнения: " . date('Y-m-d H:i:s') . "\n";

// Если запущено через браузер, выводим HTML
if (isset($_SERVER['HTTP_HOST'])) {
    echo "<br><br><strong>Готово!</strong> Вы можете закрыть эту страницу.";
}
?> 
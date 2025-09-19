<?php
/**
 * Быстрая очистка кеша Laravel
 * Упрощенная версия для хостинга
 */

// Проверяем наличие artisan
if (!file_exists('artisan')) {
    die('Ошибка: Находитесь не в корне Laravel приложения');
}

echo "Очистка кеша Laravel...\n";

// Основные команды очистки
$commands = [
    'config:clear',
    'cache:clear', 
    'route:clear',
    'view:clear'
];

foreach ($commands as $cmd) {
    echo "Выполняю: php artisan $cmd\n";
    exec("php artisan $cmd 2>&1", $output, $return);
    
    if ($return === 0) {
        echo "✓ $cmd - успешно\n";
    } else {
        echo "✗ $cmd - ошибка\n";
    }
}

// Создаем кеш конфигурации для оптимизации
echo "\nСоздаю кеш конфигурации для оптимизации...\n";
exec("php artisan config:cache 2>&1", $output, $return);
if ($return === 0) {
    echo "✓ Кеш конфигурации создан\n";
} else {
    echo "✗ Ошибка создания кеша конфигурации\n";
}

echo "\nГотово! " . date('Y-m-d H:i:s') . "\n";

// Для браузера
if (isset($_SERVER['HTTP_HOST'])) {
    echo "<br><strong>Кеш очищен!</strong>";
}
?> 
<?php
/**
 * Быстрая очистка кеша Laravel
 * Доступ: http://ваш-сайт.com/quick-clear.php
 */

// Переходим в корень проекта
chdir(__DIR__ . '/../');

// Проверяем наличие artisan
if (!file_exists('artisan')) {
    die('Ошибка: Файл artisan не найден');
}

echo "<!DOCTYPE html>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <title>Быстрая очистка кеша</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f8f8f8; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧹 Быстрая очистка кеша Laravel</h1>
        <pre>";

// Выполняем очистку кеша
$commands = [
    'config:clear' => 'Очистка конфигурации',
    'cache:clear' => 'Очистка кеша приложения',
    'route:clear' => 'Очистка маршрутов',
    'view:clear' => 'Очистка представлений'
];

$all_success = true;

foreach ($commands as $cmd => $desc) {
    echo "<span class='info'>$desc...</span>\n";
    
    $output = [];
    $return_var = 0;
    exec("php artisan $cmd 2>&1", $output, $return_var);
    
    if ($return_var === 0) {
        echo "<span class='success'>✓ $cmd - успешно</span>\n";
    } else {
        echo "<span class='error'>✗ $cmd - ошибка</span>\n";
        if (!empty($output)) {
            echo "<span class='error'>Детали: " . implode("\n", $output) . "</span>\n";
        }
        $all_success = false;
    }
    echo "\n";
}

// Создаем кеш конфигурации
echo "<span class='info'>Создание кеша конфигурации...</span>\n";
exec("php artisan config:cache 2>&1", $output, $return_var);
if ($return_var === 0) {
    echo "<span class='success'>✓ Кеш конфигурации создан</span>\n";
} else {
    echo "<span class='error'>✗ Ошибка создания кеша конфигурации</span>\n";
}

echo "\n";

if ($all_success) {
    echo "<span class='success'>🎉 Все операции выполнены успешно!</span>\n";
} else {
    echo "<span class='error'>⚠️ Есть ошибки. Проверьте детали выше.</span>\n";
}

echo "\n<span class='info'>Время: " . date('Y-m-d H:i:s') . "</span>\n";

echo "</pre>
        <p><strong>Готово!</strong> Кеш очищен и оптимизирован.</p>
        <p><small>⚠️ Удалите этот файл после использования на продакшн сервере!</small></p>
        <p><a href='javascript:history.back()'>← Назад</a></p>
    </div>
</body>
</html>";
?> 
<?php
/**
 * Автоматическая очистка кеша для cron
 * Минимальная версия без вывода
 */

// Переходим в директорию скрипта
chdir(__DIR__);

// Проверяем наличие artisan
if (!file_exists('artisan')) {
    exit(1);
}

// Команды для выполнения
$commands = [
    'php artisan config:clear',
    'php artisan cache:clear',
    'php artisan route:clear', 
    'php artisan view:clear',
    'php artisan config:cache'
];

// Выполняем команды
foreach ($commands as $cmd) {
    exec($cmd . ' 2>/dev/null', $output, $return);
    if ($return !== 0) {
        // Логируем ошибку (опционально)
        error_log("Ошибка выполнения: $cmd");
    }
}

exit(0);
?> 
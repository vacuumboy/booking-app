<?php
/**
 * Информация о кеше Laravel
 * Доступ: http://ваш-сайт.com/cache-info.php
 */

// Переходим в корень проекта
chdir(__DIR__ . '/../');

// Проверяем наличие artisan
if (!file_exists('artisan')) {
    die('Ошибка: Файл artisan не найден');
}

function formatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

function getDirectorySize($path) {
    $size = 0;
    if (is_dir($path)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
    }
    return $size;
}

function countFiles($path) {
    $count = 0;
    if (is_dir($path)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            if ($file->isFile() && $file->getFilename() !== '.gitignore') {
                $count++;
            }
        }
    }
    return $count;
}

$cacheLocations = [
    'bootstrap/cache/' => [
        'name' => 'Кеш загрузки',
        'description' => 'Кеш конфигурации, сервисов и пакетов',
        'commands' => ['config:clear', 'optimize:clear']
    ],
    'storage/framework/cache/' => [
        'name' => 'Кеш данных',
        'description' => 'Кешированные данные приложения',
        'commands' => ['cache:clear']
    ],
    'storage/framework/views/' => [
        'name' => 'Кеш представлений',
        'description' => 'Скомпилированные Blade шаблоны',
        'commands' => ['view:clear']
    ],
    'storage/framework/sessions/' => [
        'name' => 'Сессии',
        'description' => 'Файлы пользовательских сессий',
        'commands' => ['session:clear']
    ]
];

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Информация о кеше Laravel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .cache-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .cache-item { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; }
        .cache-item h3 { margin: 0 0 10px 0; color: #007bff; }
        .cache-item p { margin: 5px 0; color: #666; }
        .size-large { color: #dc3545; font-weight: bold; }
        .size-medium { color: #ffc107; font-weight: bold; }
        .size-small { color: #28a745; }
        .commands { background: #e9ecef; padding: 8px; border-radius: 4px; margin: 10px 0; font-family: monospace; font-size: 12px; }
        .btn { background: #007bff; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; margin: 5px 0; text-decoration: none; display: inline-block; }
        .btn:hover { background: #0056b3; }
        .btn.danger { background: #dc3545; }
        .btn.danger:hover { background: #c82333; }
        .summary { background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .file-list { max-height: 200px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .file-item { font-family: monospace; font-size: 12px; margin: 2px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Информация о кеше Laravel</h1>
        
        <div class="summary">
            <h3>📋 Общая информация</h3>
            <?php
            $totalSize = 0;
            $totalFiles = 0;
            foreach ($cacheLocations as $path => $info) {
                $size = getDirectorySize($path);
                $files = countFiles($path);
                $totalSize += $size;
                $totalFiles += $files;
            }
            ?>
            <p><strong>Общий размер кеша:</strong> <?= formatBytes($totalSize) ?></p>
            <p><strong>Общее количество файлов:</strong> <?= $totalFiles ?></p>
            <p><strong>Время проверки:</strong> <?= date('Y-m-d H:i:s') ?></p>
        </div>

        <div class="cache-grid">
            <?php foreach ($cacheLocations as $path => $info): ?>
                <?php
                $size = getDirectorySize($path);
                $files = countFiles($path);
                $sizeClass = $size > 1024*1024 ? 'size-large' : ($size > 1024*10 ? 'size-medium' : 'size-small');
                ?>
                <div class="cache-item">
                    <h3><?= $info['name'] ?></h3>
                    <p><?= $info['description'] ?></p>
                    <p><strong>Путь:</strong> <code><?= $path ?></code></p>
                    <p><strong>Размер:</strong> <span class="<?= $sizeClass ?>"><?= formatBytes($size) ?></span></p>
                    <p><strong>Файлов:</strong> <?= $files ?></p>
                    
                    <div class="commands">
                        <strong>Команды очистки:</strong><br>
                        <?php foreach ($info['commands'] as $cmd): ?>
                            php artisan <?= $cmd ?><br>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($files > 0): ?>
                        <div class="file-list">
                            <strong>Файлы:</strong><br>
                            <?php
                            if (is_dir($path)) {
                                $filesList = [];
                                $files = new RecursiveIteratorIterator(
                                    new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
                                );
                                
                                foreach ($files as $file) {
                                    if ($file->isFile() && $file->getFilename() !== '.gitignore') {
                                        $relativePath = str_replace($path, '', $file->getPathname());
                                        $filesList[] = [
                                            'name' => $relativePath,
                                            'size' => $file->getSize()
                                        ];
                                    }
                                }
                                
                                // Сортируем по размеру
                                usort($filesList, function($a, $b) {
                                    return $b['size'] - $a['size'];
                                });
                                
                                foreach (array_slice($filesList, 0, 10) as $file) {
                                    echo '<div class="file-item">' . 
                                         htmlspecialchars($file['name']) . 
                                         ' (' . formatBytes($file['size']) . ')' . 
                                         '</div>';
                                }
                                
                                if (count($filesList) > 10) {
                                    echo '<div class="file-item">... и еще ' . (count($filesList) - 10) . ' файлов</div>';
                                }
                            }
                            ?>
                        </div>
                    <?php else: ?>
                        <p style="color: #28a745;">✅ Пустой (только .gitignore)</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin: 20px 0;">
            <a href="clear-cache.php" class="btn">🧹 Очистить кеш</a>
            <a href="quick-clear.php" class="btn">⚡ Быстрая очистка</a>
            <a href="?refresh=1" class="btn">🔄 Обновить информацию</a>
        </div>
        
        <div class="summary">
            <h3>💡 Рекомендации</h3>
            <ul>
                <li><strong>Размер > 1MB:</strong> Рекомендуется периодическая очистка</li>
                <li><strong>Много файлов представлений:</strong> Нормально для активного сайта</li>
                <li><strong>Большой кеш данных:</strong> Проверьте логику кеширования в приложении</li>
                <li><strong>Много сессий:</strong> Настройте автоочистку старых сессий</li>
            </ul>
        </div>
        
        <p><small>⚠️ <strong>Безопасность:</strong> Удалите этот файл после использования на продакшн сервере!</small></p>
    </div>
</body>
</html> 
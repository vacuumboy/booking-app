<?php
/**
 * Простой тест для проверки работы представлений
 */

// Переходим в корень проекта
chdir(__DIR__ . '/../');

// Проверяем, что можем загрузить Laravel
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    
    // Проверяем, что можем найти представление
    $viewPath = resource_path('views/welcome.blade.php');
    
    if (file_exists($viewPath)) {
        echo "<h1>✅ Успех!</h1>";
        echo "<p>Файл представления найден: $viewPath</p>";
        echo "<p>Размер файла: " . filesize($viewPath) . " байт</p>";
        
        // Пробуем загрузить через Laravel
        try {
            $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
            $response = $kernel->handle(
                $request = Illuminate\Http\Request::create('/', 'GET')
            );
            
            echo "<p>✅ Laravel успешно обработал запрос</p>";
            echo "<p>Статус ответа: " . $response->getStatusCode() . "</p>";
            
        } catch (Exception $e) {
            echo "<p>❌ Ошибка при обработке запроса Laravel:</p>";
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        }
        
    } else {
        echo "<h1>❌ Ошибка!</h1>";
        echo "<p>Файл представления НЕ найден: $viewPath</p>";
    }
    
} catch (Exception $e) {
    echo "<h1>❌ Критическая ошибка!</h1>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
}
?>

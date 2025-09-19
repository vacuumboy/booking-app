import './bootstrap';
import './simple-spark.js';

import Alpine from 'alpinejs';
import Focus from '@alpinejs/focus';

// Регистрируем плагин Focus
Alpine.plugin(Focus);

window.Alpine = Alpine;

Alpine.start();

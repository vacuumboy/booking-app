// Функция создания искр (существующая функция)
function createSpark(x, y, color = '#ffffff', count = 8) {
  const sparks = [];
  
  for (let i = 0; i < count; i++) {
    const angle = (2 * Math.PI * i) / count;
    const spark = document.createElement('div');
    
    spark.style.position = 'absolute';
    spark.style.width = '2px';
    spark.style.height = '10px';
    spark.style.backgroundColor = color;
    spark.style.left = x + 'px';
    spark.style.top = y + 'px';
    spark.style.pointerEvents = 'none';
    spark.style.zIndex = '1000';
    spark.style.transformOrigin = 'center bottom';
    
    document.body.appendChild(spark);
    sparks.push(spark);
    
    // Анимация
    const distance = 30;
    const endX = x + distance * Math.cos(angle);
    const endY = y + distance * Math.sin(angle);
    
    spark.animate([
      {
        transform: `translate(0, 0) scale(1)`,
        opacity: 1
      },
      {
        transform: `translate(${endX - x}px, ${endY - y}px) scale(0)`,
        opacity: 0
      }
    ], {
      duration: 400,
      easing: 'ease-out'
    }).onfinish = () => {
      spark.remove();
    };
  }
}

// Глобальная функция для использования
window.createSpark = createSpark;

// Alpine.js компонент для clickSpark
document.addEventListener('alpine:init', () => {
    Alpine.data('clickSpark', () => ({
        sparkColor: '#ffffff',
        sparkCount: 8,
        sparkSize: 10,
        duration: 400,
        canvas: null,
        ctx: null,
        
        init() {
            this.canvas = this.$refs.canvas;
            if (this.canvas) {
                this.ctx = this.canvas.getContext('2d');
                this.resizeCanvas();
                
                // Обновляем размер canvas при изменении размера окна
                window.addEventListener('resize', () => this.resizeCanvas());
            }
        },
        
        resizeCanvas() {
            if (this.canvas) {
                const rect = this.canvas.parentElement.getBoundingClientRect();
                this.canvas.width = rect.width;
                this.canvas.height = rect.height;
            }
        },
        
        handleClick(event) {
            if (!this.canvas || !this.ctx) return;
            
            const rect = this.canvas.getBoundingClientRect();
            const x = event.clientX - rect.left;
            const y = event.clientY - rect.top;
            
            this.createCanvasSparks(x, y);
        },
        
        createCanvasSparks(x, y) {
            const sparks = [];
            const sparkCount = this.sparkCount || 8;
            const sparkSize = this.sparkSize || 10;
            const duration = this.duration || 400;
            const color = this.sparkColor || '#ffffff';
            
            // Создаем данные для искр
            for (let i = 0; i < sparkCount; i++) {
                const angle = (2 * Math.PI * i) / sparkCount;
                sparks.push({
                    x: x,
                    y: y,
                    vx: Math.cos(angle) * 2,
                    vy: Math.sin(angle) * 2,
                    life: 1,
                    decay: 1 / (duration / 16), // Примерно 60fps
                    size: sparkSize
                });
            }
            
            // Анимация искр
            const animate = () => {
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                
                let aliveCount = 0;
                sparks.forEach(spark => {
                    if (spark.life > 0) {
                        aliveCount++;
                        
                        // Обновляем позицию
                        spark.x += spark.vx;
                        spark.y += spark.vy;
                        spark.life -= spark.decay;
                        
                        // Рисуем искру
                        this.ctx.save();
                        this.ctx.globalAlpha = spark.life;
                        this.ctx.fillStyle = color;
                        this.ctx.fillRect(
                            spark.x - spark.size / 2, 
                            spark.y - spark.size / 2, 
                            spark.size * spark.life, 
                            spark.size * spark.life
                        );
                        this.ctx.restore();
                    }
                });
                
                if (aliveCount > 0) {
                    requestAnimationFrame(animate);
                } else {
                    // Очищаем canvas когда анимация закончена
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                }
            };
            
            animate();
        }
    }));
});

// Автоматическое добавление эффекта к кнопкам (существующий код)
document.addEventListener('DOMContentLoaded', function() {
  // Добавляем эффект ко всем кнопкам с классом spark-button
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('spark-button')) {
      const rect = e.target.getBoundingClientRect();
      const x = e.clientX;
      const y = e.clientY;
      
      // Получаем цвет из data-атрибута или используем белый
      const color = e.target.dataset.sparkColor || '#ffffff';
      const count = parseInt(e.target.dataset.sparkCount) || 8;
      
      createSpark(x, y, color, count);
    }
  });
}); 
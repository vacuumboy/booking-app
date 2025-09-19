<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Регистрация</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e0f2fe 50%, #e0e7ff 100%);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .form-container {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 32px 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        .subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 32px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 16px;
            font-size: 14px;
            background: rgba(249, 250, 251, 0.5);
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: white;
        }
        .user-type-container {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }
        .user-type-option {
            flex: 1;
            position: relative;
        }
        .user-type-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .user-type-label {
            display: block;
            padding: 20px 16px;
            border: 2px solid #d1d5db;
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .user-type-input:checked + .user-type-label {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        .user-type-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
        }
        .user-type-desc {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }
        .submit-btn {
            width: 100%;
            padding: 12px 24px;
            background: linear-gradient(135deg, #2563eb, #4f46e5);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
        }
        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25);
        }
        .login-link {
            text-align: center;
            margin-top: 24px;
            color: #6b7280;
        }
        .login-link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            color: #1d4ed8;
        }
        .footer-text {
            text-align: center;
            margin-top: 16px;
            font-size: 12px;
            color: #9ca3af;
        }
        .footer-text a {
            color: #2563eb;
            text-decoration: underline;
        }
        .error-message {
            color: #dc2626;
            font-size: 14px;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Header -->
        <div>
            <h1 class="title">Добро пожаловать</h1>
            <p class="subtitle">Создайте свой аккаунт</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- User Type Selection -->
            <div class="form-group">
                <label class="form-label">Тип аккаунта</label>
                <div class="user-type-container">
                    <div class="user-type-option">
                        <input type="radio" name="user_type" value="master" class="user-type-input" id="master" checked>
                        <label for="master" class="user-type-label">
                            <div class="user-type-title">Мастер</div>
                            <div class="user-type-desc">Индивидуальный специалист</div>
                        </label>
                    </div>
                    <div class="user-type-option">
                        <input type="radio" name="user_type" value="salon" class="user-type-input" id="salon">
                        <label for="salon" class="user-type-label">
                            <div class="user-type-title">Салон</div>
                            <div class="user-type-desc">Студия красоты</div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="form-group">
                <label for="name" class="form-label" id="name-label">Имя</label>
                <input id="name" name="name" type="text" required class="form-input" value="{{ old('name') }}" placeholder="Введите ваше имя">
                @error('name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" required class="form-input" value="{{ old('email') }}" placeholder="your@email.com">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Телефон</label>
                <input id="phone" name="phone" type="tel" required class="form-input" value="{{ old('phone') }}" placeholder="+371 2345 6789">
                @error('phone')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="address" class="form-label">Адрес</label>
                <input id="address" name="address" type="text" required class="form-input" value="{{ old('address') }}" placeholder="Ваш адрес">
                @error('address')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <input id="password" name="password" type="password" required class="form-input" placeholder="Минимум 8 символов">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="form-input" placeholder="Повторите пароль">
                @error('password_confirmation')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">
                Создать аккаунт
            </button>
        </form>

        <!-- Login Link -->
        <div class="login-link">
            <p>
                Уже есть аккаунт? 
                <a href="{{ route('login') }}">Войти</a>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer-text">
            <p>
                Регистрируясь, вы соглашаетесь с нашими 
                <a href="#">условиями использования</a>
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const masterRadio = document.getElementById('master');
            const salonRadio = document.getElementById('salon');
            const nameLabel = document.getElementById('name-label');
            const nameInput = document.getElementById('name');

            function updateNameField() {
                if (salonRadio.checked) {
                    nameLabel.textContent = 'Название салона';
                    nameInput.placeholder = 'Введите название салона';
                } else {
                    nameLabel.textContent = 'Имя';
                    nameInput.placeholder = 'Введите ваше имя';
                }
            }

            masterRadio.addEventListener('change', updateNameField);
            salonRadio.addEventListener('change', updateNameField);

            // Инициализируем при загрузке страницы
            updateNameField();
        });
    </script>
</body>
</html>

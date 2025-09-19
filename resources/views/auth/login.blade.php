<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Вход</title>
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
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .remember-me {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #6b7280;
        }
        .remember-me input {
            margin-right: 8px;
        }
        .forgot-link {
            font-size: 14px;
            color: #2563eb;
            text-decoration: none;
        }
        .forgot-link:hover {
            color: #1d4ed8;
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
        .register-link {
            text-align: center;
            margin-top: 24px;
            color: #6b7280;
        }
        .register-link a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }
        .register-link a:hover {
            color: #1d4ed8;
        }
        .error-message {
            color: #dc2626;
            font-size: 14px;
            margin-top: 4px;
        }
        .success-message {
            color: #16a34a;
            font-size: 14px;
            margin-bottom: 16px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <!-- Header -->
        <div>
            <h1 class="title">С возвращением</h1>
            <p class="subtitle">Войдите в свой аккаунт</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="success-message">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input id="email" name="email" type="email" required autofocus class="form-input" value="{{ old('email') }}" placeholder="your@email.com">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password" class="form-label">Пароль</label>
                <input id="password" name="password" type="password" required class="form-input" placeholder="Введите пароль">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="remember-forgot">
                <label for="remember_me" class="remember-me">
                    <input id="remember_me" type="checkbox" name="remember">
                    Запомнить меня
                </label>

                @if (Route::has('password.request'))
                    <a class="forgot-link" href="{{ route('password.request') }}">
                        Забыли пароль?
                    </a>
                @endif
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">
                Войти
            </button>
        </form>

        <!-- Register Link -->
        <div class="register-link">
            <p>
                Нет аккаунта? 
                <a href="{{ route('register') }}">Зарегистрируйтесь</a>
            </p>
        </div>
    </div>
</body>
</html>

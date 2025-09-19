<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'address' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_type' => ['required', 'string', 'in:master,salon'],
        ], [
            'name.required' => 'Поле "Имя" обязательно для заполнения.',
            'name.max' => 'Имя не должно превышать 255 символов.',
            'email.required' => 'Поле "Email" обязательно для заполнения.',
            'email.email' => 'Пожалуйста, введите корректный email адрес.',
            'email.unique' => 'Этот email уже зарегистрирован.',
            'phone.required' => 'Поле "Телефон" обязательно для заполнения.',
            'phone.unique' => 'Этот номер телефона уже зарегистрирован.',
            'address.required' => 'Поле "Адрес" обязательно для заполнения.',
            'password.required' => 'Поле "Пароль" обязательно для заполнения.',
            'password.confirmed' => 'Пароли не совпадают.',
            'user_type.required' => 'Необходимо выбрать тип аккаунта.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'is_active' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}

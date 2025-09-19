<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Основная информация успешно обновлена!');
    }

    /**
     * Update professional information.
     */
    public function updateProfessional(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        if ($user->isMaster()) {
            $validated = $request->validate([
                'specialization' => 'nullable|string|max:255',
                'experience_years' => 'nullable|integer|min:0|max:50',
                'bio' => 'nullable|string|max:1000',
                'certificates' => 'nullable|array',
                'certificates.*' => 'string|max:255',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'specialization.max' => 'Специализация не должна превышать 255 символов.',
                'experience_years.integer' => 'Опыт работы должен быть числом.',
                'experience_years.min' => 'Опыт работы не может быть отрицательным.',
                'experience_years.max' => 'Опыт работы не может превышать 50 лет.',
                'bio.max' => 'Описание не должно превышать 1000 символов.',
                'photo.image' => 'Файл должен быть изображением.',
                'photo.mimes' => 'Поддерживаются форматы: jpeg, png, jpg, gif.',
                'photo.max' => 'Размер изображения не должен превышать 2MB.',
            ]);
        } else {
            $validated = $request->validate([
                'salon_name' => 'nullable|string|max:255',
                'salon_license' => 'nullable|string|max:255',
                'bio' => 'nullable|string|max:1000',
                'working_hours' => 'nullable|array',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'salon_name.max' => 'Название салона не должно превышать 255 символов.',
                'salon_license.max' => 'Номер лицензии не должен превышать 255 символов.',
                'bio.max' => 'Описание не должно превышать 1000 символов.',
                'photo.image' => 'Файл должен быть изображением.',
                'photo.mimes' => 'Поддерживаются форматы: jpeg, png, jpg, gif.',
                'photo.max' => 'Размер изображения не должен превышать 2MB.',
            ]);
        }

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->photo_path) {
                Storage::disk('public')->delete($user->photo_path);
            }
            
            $path = $request->file('photo')->store('photos', 'public');
            $validated['photo_path'] = $path;
        }

        $user->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Профессиональная информация успешно обновлена!');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Пожалуйста, введите пароль для подтверждения.',
            'password.current_password' => 'Введённый пароль неверен.',
        ]);

        $user = $request->user();

        // Delete user's photo if exists
        if ($user->photo_path) {
            Storage::disk('public')->delete($user->photo_path);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

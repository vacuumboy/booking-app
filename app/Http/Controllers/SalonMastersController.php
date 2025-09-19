<?php

namespace App\Http\Controllers;

use App\Models\Master;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SalonMastersController extends Controller
{
    /**
     * Display a listing of the salon's masters.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        if (!$user->isSalon()) {
            abort(403, 'Только салоны могут управлять мастерами');
        }
        
        // Получаем мастеров через новую связь многие-ко-многим
        $masters = $user->masters()->orderBy('masters.name')->get();
            
        return view('salon.masters.index', compact('masters'));
    }
    
    /**
     * Remove a master from the salon.
     */
    public function removeMaster(Request $request, Master $master)
    {
        $user = $request->user();
        
        if (!$user->isSalon()) {
            abort(403, 'Только салоны могут удалять мастеров');
        }
        
        // Проверяем, что мастер связан с салоном через новую таблицу
        if (!$user->masters()->where('masters.id', $master->id)->exists()) {
            abort(403, 'Этот мастер не принадлежит вашему салону');
        }
        
        $name = $master->name;
        
        // Удаляем связь мастера с салоном, а не самого мастера
        $user->masters()->detach($master->id);
        
        return redirect()->route('salon.masters.index')
            ->with('success', "Мастер {$name} успешно удален из вашего салона");
    }
    
    /**
     * Create a new master for the salon.
     */
    public function create(): View
    {
        return view('salon.masters.create');
    }
    
    /**
     * Store a newly created master for the salon.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        
        if (!$user->isSalon()) {
            abort(403, 'Только салоны могут создавать мастеров');
        }
        
        // Убираем проверку уникальности email и телефона
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'bio' => 'nullable|string|max:1000',
        ], [
            'name.required' => 'Имя мастера обязательно для заполнения',
            'phone.required' => 'Телефон обязателен для заполнения',
            'email.required' => 'Email обязателен для заполнения',
            'email.email' => 'Введите корректный email адрес',
            'bio.max' => 'Описание не должно превышать 1000 символов',
        ]);
        
        // Проверяем, существует ли уже мастер с такими данными
        $existingMaster = Master::where('email', $validated['email'])
            ->orWhere('phone', $validated['phone'])
            ->first();
            
        if ($existingMaster) {
            // Если мастер существует, проверяем, не связан ли он уже с этим салоном
            if ($user->masters()->where('masters.id', $existingMaster->id)->exists()) {
                return redirect()->route('salon.masters.index')
                    ->with('error', "Мастер с таким email или телефоном уже добавлен в ваш салон");
            }
            
            // Если мастер существует, но не связан с этим салоном, создаем связь
            $user->masters()->attach($existingMaster->id, ['is_active' => true]);
            
            return redirect()->route('salon.masters.index')
                ->with('success', 'Мастер {$existingMaster->name} успешно добавлен в ваш салон');
        }
        
        // Если мастера нет, создаем нового
        $validated['is_active'] = true;
        $master = Master::create($validated);
        
        // Создаем связь с салоном через новую таблицу
        $user->masters()->attach($master->id, ['is_active' => true]);
        
        return redirect()->route('salon.masters.index')
            ->with('success', "Мастер {$master->name} успешно создан и добавлен в ваш салон");
    }
}

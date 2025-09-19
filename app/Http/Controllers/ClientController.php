<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Display a listing of the clients.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        
        $query = Client::forUser(Auth::id());
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        $query->orderBy($sortBy, $sortDir);
        
        $clients = $query->paginate(15);
        
        return view('clients.index', compact('clients', 'search', 'sortBy', 'sortDir'));
    }

    /**
     * Show the form for creating a new client.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('clients')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
            ],
            'notes' => 'nullable|string|max:1000',
            'language' => 'required|string|size:2|in:ru,lv,en',
        ], [
            'name.required' => 'Имя клиента обязательно для заполнения',
            'phone.required' => 'Телефон обязателен для заполнения',
            'phone.unique' => 'Клиент с таким телефоном уже существует у вас',
            'notes.max' => 'Заметки не должны превышать 1000 символов',
            'language.required' => 'Язык обязателен для заполнения',
            'language.in' => 'Язык должен быть одним из: ru, lv, en',
        ]);
        
        // Добавляем ID текущего пользователя
        $validated['user_id'] = Auth::id();

        $client = Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', "Клиент {$client->name} успешно добавлен");
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        // Проверяем, принадлежит ли клиент текущему пользователю
        if ($client->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому клиенту');
        }
        
        // Получаем историю записей клиента
        $appointments = Appointment::with(['master', 'service'])
            ->where('client_id', $client->id)
            ->orderBy('start_time', 'desc')
            ->get();
            
        return view('clients.show', compact('client', 'appointments'));
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client)
    {
        // Проверяем, принадлежит ли клиент текущему пользователю
        if ($client->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому клиенту');
        }
        
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, Client $client)
    {
        // Проверяем, принадлежит ли клиент текущему пользователю
        if ($client->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому клиенту');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('clients')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })->ignore($client->id),
            ],
            'notes' => 'nullable|string|max:1000',
            'language' => 'required|string|size:2|in:ru,lv,en',
        ], [
            'name.required' => 'Имя клиента обязательно для заполнения',
            'phone.required' => 'Телефон обязателен для заполнения',
            'phone.unique' => 'Клиент с таким телефоном уже существует у вас',
            'notes.max' => 'Заметки не должны превышать 1000 символов',
            'language.required' => 'Язык обязателен для заполнения',
            'language.in' => 'Язык должен быть одним из: ru, lv, en',
        ]);

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', "Данные клиента {$client->name} обновлены");
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client)
    {
        // Проверяем, принадлежит ли клиент текущему пользователю
        if ($client->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этому клиенту');
        }
        
        // Проверяем, есть ли у клиента записи
        $hasAppointments = Appointment::where('client_id', $client->id)->exists();
        
        if ($hasAppointments) {
            return redirect()->route('clients.index')
                ->with('error', "Невозможно удалить клиента {$client->name}, так как у него есть записи");
        }
        
        $name = $client->name;
        $client->delete();
        
        return redirect()->route('clients.index')
            ->with('success', "Клиент {$name} успешно удален");
    }
}

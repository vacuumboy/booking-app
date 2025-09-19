<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    /**
     * Display a listing of the services.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        
        $query = Service::forUser(Auth::id());
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting
        $query->orderBy($sortBy, $sortDir);
        
        $services = $query->paginate(15);
        
        return view('services.index', compact('services', 'search', 'sortBy', 'sortDir'));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create()
    {
        return view('services.create');
    }

    /**
     * Store a newly created service in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
            ],
            'name_ru' => 'nullable|string|max:255',
            'name_lv' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:5|max:480',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Название услуги обязательно для заполнения',
            'name.unique' => 'Услуга с таким названием уже существует',
            'name_ru.max' => 'Название на русском не должно превышать 255 символов',
            'name_lv.max' => 'Название на латышском не должно превышать 255 символов',
            'name_en.max' => 'Название на английском не должно превышать 255 символов',
            'price.required' => 'Цена обязательна для заполнения',
            'price.numeric' => 'Цена должна быть числом',
            'price.min' => 'Цена не может быть отрицательной',
            'duration.required' => 'Длительность обязательна для заполнения',
            'duration.integer' => 'Длительность должна быть целым числом',
            'duration.min' => 'Длительность не может быть меньше 5 минут',
            'duration.max' => 'Длительность не может быть больше 8 часов',
            'color.regex' => 'Цвет должен быть в формате HEX (#RRGGBB)',
        ]);

        // Set default is_active if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }
        
        // Добавляем ID текущего пользователя
        $validated['user_id'] = Auth::id();

        $service = Service::create($validated);

        return redirect()->route('services.index')
            ->with('success', "Услуга \"{$service->name}\" успешно добавлена");
    }

    /**
     * Display the specified service.
     */
    public function show(Service $service)
    {
        // Проверяем, принадлежит ли услуга текущему пользователю
        if ($service->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этой услуге');
        }
        
        // Count appointments using this service
        $appointmentsCount = Appointment::where('service_id', $service->id)->count();
        
        return view('services.show', compact('service', 'appointmentsCount'));
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit(Service $service)
    {
        // Проверяем, принадлежит ли услуга текущему пользователю
        if ($service->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этой услуге');
        }
        
        return view('services.edit', compact('service'));
    }

    /**
     * Update the specified service in storage.
     */
    public function update(Request $request, Service $service)
    {
        // Проверяем, принадлежит ли услуга текущему пользователю
        if ($service->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этой услуге');
        }
        
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })->ignore($service->id),
            ],
            'name_ru' => 'nullable|string|max:255',
            'name_lv' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:5|max:480',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'Название услуги обязательно для заполнения',
            'name.unique' => 'Услуга с таким названием уже существует',
            'name_ru.max' => 'Название на русском не должно превышать 255 символов',
            'name_lv.max' => 'Название на латышском не должно превышать 255 символов',
            'name_en.max' => 'Название на английском не должно превышать 255 символов',
            'price.required' => 'Цена обязательна для заполнения',
            'price.numeric' => 'Цена должна быть числом',
            'price.min' => 'Цена не может быть отрицательной',
            'duration.required' => 'Длительность обязательна для заполнения',
            'duration.integer' => 'Длительность должна быть целым числом',
            'duration.min' => 'Длительность не может быть меньше 5 минут',
            'duration.max' => 'Длительность не может быть больше 8 часов',
            'color.regex' => 'Цвет должен быть в формате HEX (#RRGGBB)',
        ]);

        // Handle is_active checkbox
        $validated['is_active'] = $request->has('is_active');

        $service->update($validated);

        return redirect()->route('services.index')
            ->with('success', "Услуга \"{$service->name}\" успешно обновлена");
    }

    /**
     * Toggle the active status of the service.
     */
    public function toggleStatus(Service $service)
    {
        // Проверяем, принадлежит ли услуга текущему пользователю
        if ($service->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этой услуге');
        }
        
        $service->update(['is_active' => !$service->is_active]);
        
        $status = $service->is_active ? 'активирована' : 'деактивирована';
        
        return redirect()->back()
            ->with('success', "Услуга \"{$service->name}\" успешно {$status}");
    }

    /**
     * Remove the specified service from storage.
     */
    public function destroy(Service $service)
    {
        // Проверяем, принадлежит ли услуга текущему пользователю
        if ($service->user_id !== Auth::id()) {
            abort(403, 'У вас нет доступа к этой услуге');
        }
        
        // Check if service is used in appointments
        $appointmentsCount = Appointment::where('service_id', $service->id)->count();
        
        if ($appointmentsCount > 0) {
            return redirect()->route('services.index')
                ->with('error', "Нельзя удалить услугу \"{$service->name}\", так как она используется в {$appointmentsCount} записях");
        }
        
        $serviceName = $service->name;
        $service->delete();

        return redirect()->route('services.index')
            ->with('success', "Услуга \"{$serviceName}\" успешно удалена");
    }
}

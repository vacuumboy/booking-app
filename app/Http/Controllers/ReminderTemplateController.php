<?php

namespace App\Http\Controllers;


use App\Models\ReminderTemplate;
use App\Models\Appointment;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReminderTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = Auth::user()->reminderTemplates()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reminder-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $placeholders = ReminderTemplate::getAvailablePlaceholders();
        return view('reminder-templates.create', compact('placeholders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'language' => 'required|string|size:2|in:ru,lv,en',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Auth::user()->reminderTemplates()->create([
            'name' => $request->name,
            'language' => $request->language,
            'body' => $request->body,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('reminder-templates.index')
            ->with('success', 'Шаблон успешно создан');
    }

    /**
     * Display the specified resource.
     */
    public function show(ReminderTemplate $reminderTemplate)
    {
        $this->authorize('view', $reminderTemplate);
        
        $placeholders = ReminderTemplate::getAvailablePlaceholders();
        return view('reminder-templates.show', compact('reminderTemplate', 'placeholders'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReminderTemplate $reminderTemplate)
    {
        $this->authorize('update', $reminderTemplate);
        
        $placeholders = ReminderTemplate::getAvailablePlaceholders();
        return view('reminder-templates.edit', compact('reminderTemplate', 'placeholders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReminderTemplate $reminderTemplate)
    {
        $this->authorize('update', $reminderTemplate);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'language' => 'required|string|size:2|in:ru,lv,en',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $reminderTemplate->update([
            'name' => $request->name,
            'language' => $request->language,
            'body' => $request->body,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('reminder-templates.index')
            ->with('success', 'Шаблон успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReminderTemplate $reminderTemplate)
    {
        $this->authorize('delete', $reminderTemplate);
        
        $reminderTemplate->delete();

        return redirect()->route('reminder-templates.index')
            ->with('success', 'Шаблон успешно удален');
    }

    /**
     * Preview template with sample data or real appointment data
     */
    public function preview(Request $request, ReminderTemplate $reminderTemplate)
    {
        $this->authorize('view', $reminderTemplate);

        $appointmentId = $request->get('appointment_id');
        
        if ($appointmentId) {
            $appointment = Appointment::with(['client', 'service', 'master.user', 'createdBy'])
                ->find($appointmentId);
                
            if ($appointment) {
                $filledTemplate = $reminderTemplate->fillTemplate($appointment);
            } else {
                $filledTemplate = $this->getSampleTemplate($reminderTemplate);
            }
        } else {
            $filledTemplate = $this->getSampleTemplate($reminderTemplate);
        }

        return response()->json([
            'preview' => $filledTemplate,
            'template_name' => $reminderTemplate->name,
        ]);
    }

    /**
     * Get active templates for modal selection
     */
    public function getActiveTemplates(Request $request)
    {
        $clientId = $request->get('client_id');
        $clientLanguage = 'ru'; // По умолчанию русский
        
        // Получаем язык клиента, если указан ID
        if ($clientId) {
            $client = Client::find($clientId);
            if ($client) {
                $clientLanguage = $client->language;
            }
        }
        
        $templates = Auth::user()->activeReminderTemplates()
            ->select('id', 'name', 'body', 'language')
            ->get()
            ->sortBy(function ($template) use ($clientLanguage) {
                // Сортируем: сначала шаблоны на языке клиента, затем остальные
                return $template->language === $clientLanguage ? 0 : 1;
            })
            ->values();

        return response()->json($templates);
    }

    /**
     * Generate sample template with placeholder data
     */
    private function getSampleTemplate(ReminderTemplate $template): string
    {
        $language = $template->language ?? 'ru';
        
        $sampleData = [
            '{client_name}' => 'Николь',
            '{service_name}' => $this->getSampleServiceName($language),
            '{date_time}' => $this->getSampleDateTime($language),
            '{price}' => '25 euro',
            '{studio_address}' => 'ул. Красная, 15',
        ];

        return str_replace(array_keys($sampleData), array_values($sampleData), $template->body);
    }
    
    /**
     * Get sample service name based on language
     */
    private function getSampleServiceName($language): string
    {
        switch ($language) {
            case 'ru':
                return 'Маникюр';
            case 'lv':
                return 'Manikīrs';
            case 'en':
                return 'Manicure';
            default:
                return 'Маникюр';
        }
    }
    
    /**
     * Get sample date time based on language
     */
    private function getSampleDateTime($language): string
    {
        switch ($language) {
            case 'ru':
                return '15.07.2025 в 14:30';
            case 'lv':
                return '15.07.2025 plkst. 14:30';
            case 'en':
                return '15.07.2025 at 14:30';
            default:
                return '15.07.2025 в 14:30';
        }
    }
}

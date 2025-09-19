<?php

namespace App\Providers;

use App\Models\Schedule;
use App\Models\ReminderTemplate;
use App\Policies\SchedulePolicy;
use App\Policies\ReminderTemplatePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Schedule::class => SchedulePolicy::class,
        ReminderTemplate::class => ReminderTemplatePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
} 
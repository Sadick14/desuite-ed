<?php

namespace App\Providers;


use App\Models\AcademicYear;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Term;
use App\Observers\AuditLogObserver;
use App\Observers\StudentEnrollmentObserver;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\URL; // <-- Make sure to import this

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerObservers();
        // Force HTTPS if the application environment is set to production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Register model observers for audit logging.
     */
    protected function registerObservers(): void
    {
        // Audit logging observers
        Student::observe(AuditLogObserver::class);
        Payment::observe(AuditLogObserver::class);
        Expense::observe(AuditLogObserver::class);
        ExpenseCategory::observe(AuditLogObserver::class);
        FeeStructure::observe(AuditLogObserver::class);
        Term::observe(AuditLogObserver::class);
        AcademicYear::observe(AuditLogObserver::class);
        SchoolClass::observe(AuditLogObserver::class);
        StudentEnrollment::observe(AuditLogObserver::class);

        // Data integrity observers
        StudentEnrollment::observe(StudentEnrollmentObserver::class);
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}

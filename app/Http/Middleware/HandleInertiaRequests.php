<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $permissions = [];

        if ($user && $user->role) {
            $permissions = array_map(fn ($perm) => $perm->value, $user->role->permissions());
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'role' => $user?->role?->value,
                'permissions' => $permissions,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'currentTeam' => fn () => $user?->currentTeam ? $user->toUserTeam($user->currentTeam) : null,
            'teams' => fn () => $user?->toUserTeams(includeCurrent: true) ?? [],
            'routes' => $this->getRoutes(),
        ];
    }

    protected function getRoutes(): array
    {
        return [
            'students.index' => route('students.index'),
            'payments.index' => route('payments.index'),
            'payments.store' => route('payments.store'),
            'payments.storeBulk' => route('payments.storeBulk'),
            'payments.destroy' => route('payments.destroy', ['payment' => 0]),
            'reports.index' => route('reports.index'),
            'student-marks.index' => route('student-marks.index'),
            'assessment-settings.index' => route('assessment-settings.index'),
            'grading-scales.index' => route('grading-scales.index'),
            'courses.index' => route('courses.index'),
        ];
    }
}

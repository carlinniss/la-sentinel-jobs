<?php

declare(strict_types=1);

namespace Modules\Site\App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResumeOnlyMode
{
    private const ALLOWED_ROUTES = [
        'resume.*',
        'resumes.*',
        'login',
        'logout',
        'password.*',
        'verification.*',
        'auth.social.*',
        'profile.*',
        'panel.profile.edit',
        'legal.*',
        'lang.switch',
        'locations.*',
        'livewire.*',
    ];

    public static function boardEnabled(): bool
    {
        return (bool) config('resume.jobs_board_enabled', false);
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (self::boardEnabled() || $request->routeIs(...self::ALLOWED_ROUTES) || $request->is('livewire/*', 'up')) {
            return $next($request);
        }

        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            abort(404);
        }

        return redirect()->route('resume.post', $request->only('src'));
    }
}

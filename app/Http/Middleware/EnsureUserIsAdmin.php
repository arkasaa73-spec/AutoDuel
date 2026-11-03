<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Пропускает только пользователей с ролью admin, остальным отдаёт 403
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->role === 'admin', 403, 'Доступ только для администратора.');

        return $next($request);
    }
}

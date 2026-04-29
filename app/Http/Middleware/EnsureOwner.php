<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isOwner()) {
            abort(403, 'Chỉ chủ quán mới có quyền truy cập trang này.');
        }

        return $next($request);
    }
}

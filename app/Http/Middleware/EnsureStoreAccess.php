<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Owner: chỉ truy cập store của mình
        if ($user->isOwner() && ! session()->has('active_store_id')) {
            $store = $user->stores()->where('is_active', true)->first();

            if (! $store) {
                return redirect()->route('stores.create')
                    ->with('info', 'Vui lòng tạo cửa hàng trước.');
            }

            session(['active_store_id' => $store->id]);
        }

        // Staff: kiểm tra store được giao
        if ($user->isStaff()) {
            $employee = $user->stores()
                ->getRelated()
                ->newQuery()
                ->whereHas('employees', fn ($q) => $q->where('user_id', $user->id)->where('is_active', true))
                ->first();

            if (! $employee) {
                abort(403, 'Tài khoản chưa được gắn với cửa hàng nào.');
            }

            if (! session()->has('active_store_id')) {
                session(['active_store_id' => $employee->id]);
            }
        }

        return $next($request);
    }
}

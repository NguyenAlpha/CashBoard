<?php

namespace App\Http\Middleware;

use App\Helpers\StoreContext;
use App\Models\Employee;
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

        // Owner: tự động chọn store đầu tiên nếu chưa có active store
        if ($user->isOwner() && ! session()->has('active_store_id')) {
            $store = $user->stores()->where('is_active', true)->first();

            if (! $store) {
                return redirect()->route('stores.create')
                    ->with('info', 'Vui lòng tạo cửa hàng trước.');
            }

            StoreContext::activate($store);
        }

        // Staff: lấy store qua bảng employees
        if ($user->isStaff() && ! session()->has('active_store_id')) {
            $employee = Employee::where('user_id', $user->id)
                ->where('is_active', true)
                ->with('store')
                ->first();

            if (! $employee) {
                abort(403, 'Tài khoản chưa được gắn với cửa hàng nào.');
            }

            StoreContext::activate($employee->store);
        }

        return $next($request);
    }
}

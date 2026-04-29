<?php

namespace App\Http\Controllers;

use App\Helpers\StoreContext;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function open(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        // Kiểm tra employee thuộc store này
        $employee = Employee::where('id', $data['employee_id'])
            ->where('store_id', StoreContext::id())
            ->where('is_active', true)
            ->firstOrFail();

        Shift::create([
            'store_id'    => StoreContext::id(),
            'employee_id' => $employee->id,
            'name'        => $data['name'],
            'started_at'  => now(),
        ]);

        return redirect()->back()
            ->with('success', "Đã mở ca \"{$data['name']}\" cho {$employee->name}.");
    }

    public function close(Shift $shift): RedirectResponse
    {
        $this->authorizeShift($shift);

        if (! $shift->isOpen()) {
            return redirect()->back()->with('error', 'Ca này đã đóng rồi.');
        }

        $shift->update(['ended_at' => now()]);

        return redirect()->back()
            ->with('success', "Đã đóng ca \"{$shift->name}\".");
    }

    private function authorizeShift(Shift $shift): void
    {
        if ($shift->store_id !== StoreContext::id()) {
            abort(403);
        }
    }
}

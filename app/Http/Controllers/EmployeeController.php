<?php

namespace App\Http\Controllers;

use App\Helpers\StoreContext;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = Employee::where('store_id', StoreContext::id())
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return view('employees.index', compact('employees'));
    }

    public function create(): View
    {
        return view('employees.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
        ]);

        // Tạo user account cho staff
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'],
            'role'     => 'staff',
        ]);

        Employee::create([
            'store_id'  => StoreContext::id(),
            'user_id'   => $user->id,
            'name'      => $data['name'],
            'role'      => 'staff',
            'is_active' => true,
        ]);

        return redirect()->route('employees.index')
            ->with('success', "Đã thêm nhân viên \"{$data['name']}\".");
    }

    public function edit(Employee $employee): View
    {
        $this->authorizeEmployee($employee);

        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeEmployee($employee);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $employee->update($data);

        // Đồng bộ tên vào user account
        $employee->user?->update(['name' => $data['name']]);

        return redirect()->route('employees.index')
            ->with('success', "Đã cập nhật nhân viên \"{$employee->name}\".");
    }

    public function toggleActive(Employee $employee): RedirectResponse
    {
        $this->authorizeEmployee($employee);

        $employee->update(['is_active' => ! $employee->is_active]);

        $status = $employee->is_active ? 'kích hoạt' : 'vô hiệu hoá';

        return redirect()->route('employees.index')
            ->with('success', "Đã {$status} nhân viên \"{$employee->name}\".");
    }

    private function authorizeEmployee(Employee $employee): void
    {
        if ($employee->store_id !== StoreContext::id()) {
            abort(403);
        }
    }
}

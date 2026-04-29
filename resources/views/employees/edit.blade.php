@extends('layouts.app')

@section('title', 'Sửa nhân viên')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Sửa nhân viên</h1>
        <a href="{{ route('employees.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
            ← Danh sách
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-6 max-w-xl">
        <form method="POST" action="{{ route('employees.update', $employee) }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Họ tên <span class="text-red-500">*</span>
                </label>
                <input id="name" type="text" name="name" value="{{ old('name', $employee->name) }}" required autofocus
                    class="w-full rounded-lg border px-3 py-2.5 text-sm outline-none transition
                           @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror
                           focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <input id="is_active" type="checkbox" name="is_active" value="1"
                    @checked(old('is_active', $employee->is_active))
                    class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400">
                <label for="is_active" class="text-sm text-gray-700">Đang làm việc</label>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg px-5 py-2.5 text-sm transition">
                    Lưu thay đổi
                </button>
                <a href="{{ route('employees.index') }}" class="text-sm text-gray-500 hover:underline">Huỷ</a>
            </div>
        </form>
    </div>
@endsection

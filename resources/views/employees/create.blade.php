@extends('layouts.app')

@section('title', 'Thêm nhân viên')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Thêm nhân viên</h1>
        <a href="{{ route('employees.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
            ← Danh sách
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-6 max-w-xl">
        <p class="text-sm text-gray-500 mb-5">
            Nhân viên sẽ có tài khoản đăng nhập riêng để nhập ca và tiền mặt.
        </p>

        <form method="POST" action="{{ route('employees.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Họ tên <span class="text-red-500">*</span>
                </label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full rounded-lg border px-3 py-2.5 text-sm outline-none transition
                           @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror
                           focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                    placeholder="Nguyễn Văn A">
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                    Email đăng nhập <span class="text-red-500">*</span>
                </label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    class="w-full rounded-lg border px-3 py-2.5 text-sm outline-none transition
                           @error('email') border-red-400 bg-red-50 @else border-gray-300 @enderror
                           focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                    placeholder="nhanvien@example.com">
                @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                    Mật khẩu <span class="text-red-500">*</span>
                </label>
                <input id="password" type="text" name="password" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none transition
                           focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                    placeholder="Tối thiểu 8 ký tự">
                <p class="mt-1 text-xs text-gray-400">Giao mật khẩu này cho nhân viên để họ đăng nhập.</p>
                @error('password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg px-5 py-2.5 text-sm transition">
                    Thêm nhân viên
                </button>
                <a href="{{ route('employees.index') }}" class="text-sm text-gray-500 hover:underline">Huỷ</a>
            </div>
        </form>
    </div>
@endsection

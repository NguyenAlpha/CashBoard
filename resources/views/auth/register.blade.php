@extends('layouts.guest')

@section('title', 'Đăng ký')

@section('content')
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Tạo tài khoản chủ quán</h2>
    <p class="text-sm text-gray-500 mb-6">Miễn phí trong 30 ngày đầu.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        {{-- Tên --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                Họ tên
            </label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                class="w-full rounded-lg border px-3 py-2.5 text-sm outline-none transition
                       @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror
                       focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                placeholder="Nguyễn Văn A"
            >
            @error('name')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                Email
            </label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="email"
                class="w-full rounded-lg border px-3 py-2.5 text-sm outline-none transition
                       @error('email') border-red-400 bg-red-50 @else border-gray-300 @enderror
                       focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                placeholder="email@example.com"
            >
            @error('email')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                Mật khẩu
            </label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none transition
                       focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                placeholder="Tối thiểu 8 ký tự"
            >
            @error('password')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                Nhập lại mật khẩu
            </label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none transition
                       focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                placeholder="••••••••"
            >
        </div>

        <button
            type="submit"
            class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg py-2.5 text-sm transition"
        >
            Tạo tài khoản
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Đã có tài khoản?
        <a href="{{ route('login') }}" class="text-orange-500 hover:underline font-medium">
            Đăng nhập
        </a>
    </p>
@endsection

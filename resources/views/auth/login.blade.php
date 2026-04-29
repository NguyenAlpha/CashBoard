@extends('layouts.guest')

@section('title', 'Đăng nhập')

@section('content')
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Đăng nhập</h2>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

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
                autofocus
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
                autocomplete="current-password"
                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none transition
                       focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                placeholder="••••••••"
            >
            @error('password')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember me --}}
        <div class="flex items-center gap-2">
            <input
                id="remember"
                type="checkbox"
                name="remember"
                class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400"
            >
            <label for="remember" class="text-sm text-gray-600">Ghi nhớ đăng nhập</label>
        </div>

        <button
            type="submit"
            class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg py-2.5 text-sm transition"
        >
            Đăng nhập
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Chưa có tài khoản?
        <a href="{{ route('register') }}" class="text-orange-500 hover:underline font-medium">
            Đăng ký ngay
        </a>
    </p>
@endsection

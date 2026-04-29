<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CashBoard') – CashBoard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-800">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-gray-200 px-4 py-3">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="text-xl font-bold text-orange-500 tracking-tight">
                CashBoard
            </a>

            <div class="flex items-center gap-4 text-sm">
                @auth
                    {{-- Tên store đang active --}}
                    @if(session('active_store_name'))
                        <a href="{{ route('stores.index') }}"
                            class="text-gray-600 hover:text-orange-500 transition font-medium hidden sm:inline">
                            🏪 {{ session('active_store_name') }}
                        </a>
                    @endif

                    <span class="text-gray-400 hidden sm:inline">|</span>
                    <span class="text-gray-600 hidden sm:inline">{{ auth()->user()->name }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="text-gray-500 hover:text-gray-800 transition">
                            Đăng xuất
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="max-w-5xl mx-auto mt-4 px-4">
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-5xl mx-auto mt-4 px-4">
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        </div>
    @endif

    @if(session('info'))
        <div class="max-w-5xl mx-auto mt-4 px-4">
            <div class="bg-blue-50 border border-blue-200 text-blue-700 rounded-lg px-4 py-3 text-sm">
                {{ session('info') }}
            </div>
        </div>
    @endif

    {{-- Main content --}}
    <main class="max-w-5xl mx-auto px-4 py-6">
        @yield('content')
    </main>

</body>
</html>

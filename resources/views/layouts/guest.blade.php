<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CashBoard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-orange-50 flex items-center justify-center px-4">

    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-orange-500 tracking-tight">CashBoard</h1>
            <p class="text-gray-500 text-sm mt-1">Quản lý dòng tiền cho quán nhỏ</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            @yield('content')
        </div>
    </div>

</body>
</html>

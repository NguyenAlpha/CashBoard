@extends('layouts.guest')

@section('title', 'Tạo cửa hàng')

@section('content')
    <h2 class="text-xl font-semibold text-gray-800 mb-2">Tạo cửa hàng đầu tiên</h2>
    <p class="text-sm text-gray-500 mb-6">Thông tin này có thể chỉnh sửa sau.</p>

    {{-- Placeholder — sẽ implement đầy đủ ở TASK-03 --}}
    <div class="bg-orange-50 border border-orange-200 text-orange-700 rounded-lg px-4 py-3 text-sm">
        Tính năng đang xây dựng — TASK-03
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:underline">
            Bỏ qua, vào dashboard
        </a>
    </div>
@endsection

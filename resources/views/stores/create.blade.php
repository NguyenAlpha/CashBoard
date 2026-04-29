@extends('layouts.guest')

@section('title', 'Tạo cửa hàng')

@section('content')
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Tạo cửa hàng đầu tiên 🏪</h2>
        <p class="text-sm text-gray-500 mt-1">Thông tin này có thể chỉnh sửa sau.</p>
    </div>

    @if(session('info'))
        <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg px-4 py-3 text-sm">
            {{ session('info') }}
        </div>
    @endif

    <form method="POST" action="{{ route('stores.store') }}" class="space-y-5">
        @csrf
        @include('stores._form', ['store' => null])

        <button type="submit"
            class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg py-2.5 text-sm transition">
            Tạo cửa hàng & vào Dashboard
        </button>
    </form>
@endsection

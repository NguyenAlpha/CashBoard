@extends('layouts.app')

@section('title', 'Chỉnh sửa cửa hàng')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Chỉnh sửa cửa hàng</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $store->name }}</p>
        </div>
        <a href="{{ route('stores.index') }}"
            class="text-sm text-gray-500 hover:text-gray-700 transition">
            ← Danh sách cửa hàng
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-6 max-w-xl">
        <form method="POST" action="{{ route('stores.update', $store) }}" class="space-y-5">
            @csrf
            @method('PATCH')
            @include('stores._form', ['store' => $store])

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg px-5 py-2.5 text-sm transition">
                    Lưu thay đổi
                </button>
                <a href="{{ route('stores.index') }}"
                    class="text-sm text-gray-500 hover:underline">
                    Huỷ
                </a>
            </div>
        </form>
    </div>
@endsection

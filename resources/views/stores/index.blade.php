@extends('layouts.app')

@section('title', 'Cửa hàng của tôi')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Cửa hàng của tôi</h1>
        <a href="{{ route('stores.create') }}"
            class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg px-4 py-2 transition">
            + Thêm cửa hàng
        </a>
    </div>

    <div class="space-y-3">
        @foreach($stores as $store)
            @php $isActive = \App\Helpers\StoreContext::id() === $store->id; @endphp
            <div class="bg-white rounded-2xl border {{ $isActive ? 'border-orange-300 ring-2 ring-orange-100' : 'border-gray-200' }} p-5 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-gray-800">{{ $store->name }}</span>
                        @if($isActive)
                            <span class="text-xs bg-orange-100 text-orange-600 font-medium px-2 py-0.5 rounded-full">
                                Đang hoạt động
                            </span>
                        @endif
                        @unless($store->is_active)
                            <span class="text-xs bg-gray-100 text-gray-500 font-medium px-2 py-0.5 rounded-full">
                                Đã tắt
                            </span>
                        @endunless
                    </div>
                    @if($store->address)
                        <p class="text-sm text-gray-500 mt-0.5">{{ $store->address }}</p>
                    @endif
                    <p class="text-xs text-gray-400 mt-1">{{ $store->timezone }}</p>
                </div>

                <div class="flex items-center gap-2">
                    @unless($isActive)
                        <form method="POST" action="{{ route('stores.activate', $store) }}">
                            @csrf
                            <button type="submit"
                                class="text-sm text-orange-500 hover:text-orange-600 font-medium transition">
                                Chuyển sang
                            </button>
                        </form>
                    @endunless
                    <a href="{{ route('stores.edit', $store) }}"
                        class="text-sm text-gray-500 hover:text-gray-700 transition">
                        Chỉnh sửa
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endsection

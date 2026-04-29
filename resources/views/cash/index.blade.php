@extends('layouts.app')

@section('title', 'Nhập tiền mặt')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Tiền mặt hôm nay</h1>
        @if($openShift)
            <p class="text-sm text-green-600 mt-1">
                Ca đang mở: <strong>{{ $openShift->name }}</strong>
                — {{ $openShift->employee?->name }}
                (từ {{ $openShift->started_at->timezone($tz)->format('H:i') }})
            </p>
        @else
            <p class="text-sm text-gray-400 mt-1">Chưa có ca đang mở — giao dịch sẽ không gắn ca.</p>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Form nhập tiền mặt --}}
        <div class="md:col-span-1">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h2 class="font-semibold text-gray-700 mb-4">Nhập tiền mặt</h2>

                <form method="POST" action="{{ route('cash.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Số tiền (VNĐ) <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="amount"
                            type="number"
                            name="amount"
                            value="{{ old('amount') }}"
                            required
                            min="1"
                            autofocus
                            class="w-full rounded-lg border px-3 py-2.5 text-sm outline-none transition
                                   @error('amount') border-red-400 bg-red-50 @else border-gray-300 @enderror
                                   focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                            placeholder="500000">
                        @error('amount')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="note" class="block text-sm font-medium text-gray-700 mb-1">
                            Ghi chú <span class="text-gray-400 font-normal">(không bắt buộc)</span>
                        </label>
                        <input
                            id="note"
                            type="text"
                            name="note"
                            value="{{ old('note') }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none transition
                                   focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
                            placeholder="Buổi sáng, đếm quầy...">
                        @error('note')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="transacted_at" class="block text-sm font-medium text-gray-700 mb-1">
                            Thời điểm <span class="text-gray-400 font-normal">(mặc định: bây giờ)</span>
                        </label>
                        <input
                            id="transacted_at"
                            type="datetime-local"
                            name="transacted_at"
                            value="{{ old('transacted_at') }}"
                            max="{{ now()->timezone($tz)->format('Y-m-d\TH:i') }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none transition
                                   focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                        @error('transacted_at')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg py-2.5 text-sm transition">
                        Ghi nhận
                    </button>
                </form>
            </div>
        </div>

        {{-- Danh sách tiền mặt hôm nay --}}
        <div class="md:col-span-2">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-gray-700">Hôm nay</h2>
                    <span class="text-lg font-bold text-orange-500">
                        {{ number_format($todayTotal) }} đ
                    </span>
                </div>

                @forelse($entries as $entry)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                        <div>
                            <p class="font-medium text-gray-800">
                                {{ number_format($entry->amount) }} đ
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $entry->transacted_at->timezone($tz)->format('H:i') }}
                                @if($entry->shift)
                                    · Ca {{ $entry->shift->name }}
                                @endif
                                @if($entry->note)
                                    · {{ $entry->note }}
                                @endif
                            </p>
                        </div>

                        @if(auth()->user()->isOwner())
                            <form method="POST" action="{{ route('cash.destroy', $entry) }}"
                                onsubmit="return confirm('Xoá giao dịch này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition">
                                    Xoá
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="py-8 text-center text-gray-400">
                        <p class="text-3xl mb-2">💵</p>
                        <p class="text-sm">Chưa có tiền mặt nào hôm nay.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
@endsection

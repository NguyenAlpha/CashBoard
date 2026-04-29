@extends('layouts.app')

@section('title', 'Map cột')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Map cột dữ liệu</h1>
        <p class="text-sm text-gray-500 mt-1">
            Chỉ cần làm 1 lần — hệ thống sẽ nhớ cấu hình này cho lần sau.
        </p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-6 max-w-xl">

        {{-- Preview headers --}}
        <div class="mb-5 p-3 bg-gray-50 rounded-xl border border-gray-200 overflow-x-auto">
            <p class="text-xs text-gray-500 mb-2 font-medium">Các cột trong file của bạn:</p>
            <div class="flex flex-wrap gap-2">
                @foreach($headers as $header)
                    <span class="text-xs bg-white border border-gray-300 text-gray-600 px-2 py-1 rounded-lg">
                        {{ $header }}
                    </span>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('import.map', $batch) }}" class="space-y-4">
            @csrf

            <input type="hidden" name="source" value="{{ $source }}">

            @php
                $colOptions = array_merge([''], $headers);
            @endphp

            {{-- Số tiền (bắt buộc) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Cột <strong>Số tiền</strong> <span class="text-red-500">*</span>
                </label>
                <select name="col_amount" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none bg-white
                           focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                    @foreach($colOptions as $col)
                        <option value="{{ $col }}">{{ $col ?: '— chọn cột —' }}</option>
                    @endforeach
                </select>
                @error('col_amount')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Ngày giờ (bắt buộc) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Cột <strong>Ngày giờ giao dịch</strong> <span class="text-red-500">*</span>
                </label>
                <select name="col_date" required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none bg-white
                           focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                    @foreach($colOptions as $col)
                        <option value="{{ $col }}">{{ $col ?: '— chọn cột —' }}</option>
                    @endforeach
                </select>
                @error('col_date')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Mã tham chiếu (không bắt buộc) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Cột <strong>Mã tham chiếu</strong>
                    <span class="text-gray-400 font-normal">(dùng để chống trùng lặp)</span>
                </label>
                <select name="col_ref"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none bg-white
                           focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                    @foreach($colOptions as $col)
                        <option value="{{ $col }}">{{ $col ?: '— không có —' }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Ghi chú (không bắt buộc) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Cột <strong>Ghi chú / Nội dung</strong>
                    <span class="text-gray-400 font-normal">(không bắt buộc)</span>
                </label>
                <select name="col_note"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none bg-white
                           focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                    @foreach($colOptions as $col)
                        <option value="{{ $col }}">{{ $col ?: '— không có —' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg px-5 py-2.5 text-sm transition">
                    Bắt đầu import
                </button>
                <a href="{{ route('import.index') }}" class="text-sm text-gray-500 hover:underline">Huỷ</a>
            </div>
        </form>
    </div>
@endsection

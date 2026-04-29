@extends('layouts.app')

@section('title', 'Import sao kê')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Import sao kê</h1>
        <p class="text-sm text-gray-500 mt-1">Upload file CSV hoặc XLSX từ ngân hàng / ví điện tử.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Form upload --}}
        <div class="md:col-span-1">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h2 class="font-semibold text-gray-700 mb-4">Upload file</h2>

                <form method="POST" action="{{ route('import.upload') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nguồn thanh toán <span class="text-red-500">*</span>
                        </label>
                        <select name="source"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none bg-white
                                   focus:border-orange-400 focus:ring-2 focus:ring-orange-100">
                            <option value="bank_qr">🏦 QR Ngân hàng</option>
                            <option value="wallet">📱 Ví điện tử</option>
                            <option value="card">💳 Thẻ</option>
                        </select>
                        @error('source')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            File sao kê <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="file" accept=".csv,.xlsx,.xls" required
                            class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3
                                   file:rounded-lg file:border-0 file:bg-orange-50 file:text-orange-600
                                   file:font-medium hover:file:bg-orange-100 cursor-pointer">
                        <p class="mt-1 text-xs text-gray-400">CSV hoặc XLSX, tối đa 10MB</p>
                        @error('file')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-lg py-2.5 text-sm transition">
                        Upload & Map cột
                    </button>
                </form>
            </div>
        </div>

        {{-- Lịch sử import --}}
        <div class="md:col-span-2">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h2 class="font-semibold text-gray-700 mb-4">Lịch sử import</h2>

                @forelse($batches as $batch)
                    @php
                        $statusColor = match($batch->status) {
                            'done'       => 'bg-green-100 text-green-700',
                            'failed'     => 'bg-red-100 text-red-600',
                            'processing' => 'bg-blue-100 text-blue-600',
                            default      => 'bg-gray-100 text-gray-500',
                        };
                        $statusLabel = match($batch->status) {
                            'done'       => 'Hoàn tất',
                            'failed'     => 'Lỗi',
                            'processing' => 'Đang xử lý',
                            default      => 'Chờ',
                        };
                    @endphp
                    <div class="flex items-start justify-between py-3 border-b border-gray-100 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-700">
                                {{ basename($batch->filename) }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $batch->created_at->timezone(session('active_store_timezone', 'Asia/Ho_Chi_Minh'))->format('d/m/Y H:i') }}
                                · {{ strtoupper($batch->source_type) }}
                            </p>
                            @if($batch->status === 'done')
                                <p class="text-xs text-gray-500 mt-0.5">
                                    ✅ {{ number_format($batch->imported_count) }} giao dịch
                                    @if($batch->failed_count > 0)
                                        · ⚠️ {{ $batch->failed_count }} lỗi
                                    @endif
                                </p>
                            @endif
                            @if($batch->status === 'failed' && $batch->error_log)
                                <p class="text-xs text-red-500 mt-0.5">
                                    {{ $batch->error_log[0]['message'] ?? 'Lỗi không xác định' }}
                                </p>
                            @endif
                        </div>
                        <span class="text-xs font-medium px-2 py-1 rounded-full {{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center text-gray-400">
                        <p class="text-3xl mb-2">📂</p>
                        <p class="text-sm">Chưa có lần import nào.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

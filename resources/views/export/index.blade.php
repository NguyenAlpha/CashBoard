@extends('layouts.app')

@section('title', 'Xuất báo cáo')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Xuất báo cáo</h1>
        <p class="text-sm text-gray-400 mt-1">Tải xuống file Excel cho khoảng thời gian bạn chọn.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Export transactions --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-700 mb-1">Giao dịch chi tiết</h2>
            <p class="text-xs text-gray-400 mb-4">Xuất tất cả giao dịch trong khoảng ngày, lọc theo nguồn tiền tuỳ chọn.</p>

            <form method="GET" action="{{ route('export.transactions') }}" class="space-y-3">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Từ ngày <span class="text-red-500">*</span></label>
                        <input type="date" name="from"
                               value="{{ request('from', now()->startOfMonth()->toDateString()) }}"
                               required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                      focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Đến ngày <span class="text-red-500">*</span></label>
                        <input type="date" name="to"
                               value="{{ request('to', now()->toDateString()) }}"
                               required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                      focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nguồn tiền</label>
                    <select name="source"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                   focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none">
                        <option value="">Tất cả</option>
                        <option value="cash">Tiền mặt</option>
                        <option value="bank_qr">QR Ngân hàng</option>
                        <option value="wallet">Ví điện tử</option>
                        <option value="card">Thẻ</option>
                    </select>
                </div>

                @if($errors->any())
                    <div class="text-xs text-red-500">{{ $errors->first() }}</div>
                @endif

                <button type="submit"
                        class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium text-sm py-2.5 rounded-lg transition">
                    ↓ Tải xuống Excel
                </button>
            </form>
        </div>

        {{-- Export summary --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-700 mb-1">Báo cáo tổng hợp theo ngày</h2>
            <p class="text-xs text-gray-400 mb-4">Mỗi dòng là tổng doanh thu một ngày, chia theo từng nguồn tiền.</p>

            <form method="GET" action="{{ route('export.summary') }}" class="space-y-3">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Từ ngày <span class="text-red-500">*</span></label>
                        <input type="date" name="from"
                               value="{{ request('from', now()->startOfMonth()->toDateString()) }}"
                               required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                      focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Đến ngày <span class="text-red-500">*</span></label>
                        <input type="date" name="to"
                               value="{{ request('to', now()->toDateString()) }}"
                               required
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                                      focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none">
                    </div>
                </div>

                @if($errors->any())
                    <div class="text-xs text-red-500">{{ $errors->first() }}</div>
                @endif

                <button type="submit"
                        class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-medium text-sm py-2.5 rounded-lg transition">
                    ↓ Tải xuống Excel
                </button>
            </form>
        </div>

    </div>
@endsection

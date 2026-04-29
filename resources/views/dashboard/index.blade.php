@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Xin chào, {{ auth()->user()->name }} 👋</h1>
            <p class="text-sm text-gray-400 mt-0.5">
                {{ \Carbon\Carbon::now($tz)->translatedFormat('l, d/m/Y') }}
                · {{ session('active_store_name') }}
            </p>
        </div>
        <a href="{{ route('cash.index') }}"
           class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition self-start sm:self-auto">
            + Nhập tiền mặt
        </a>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

        @php
            $diff    = $todaySummary->total_amount - $yesterdaySummary->total_amount;
            $pct     = $yesterdaySummary->total_amount > 0
                         ? round($diff / $yesterdaySummary->total_amount * 100, 1)
                         : null;
            $diffPos = $diff >= 0;
        @endphp

        {{-- Total today --}}
        <div class="col-span-2 bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Doanh thu hôm nay</p>
            <p class="text-3xl font-bold text-gray-800">{{ number_format($todaySummary->total_amount) }} <span class="text-lg font-normal text-gray-400">đ</span></p>
            <p class="text-xs mt-1 {{ $diffPos ? 'text-green-600' : 'text-red-500' }}">
                {{ $diffPos ? '▲' : '▼' }}
                {{ number_format(abs($diff)) }} đ so với hôm qua
                @if($pct !== null)
                    ({{ $pct }}%)
                @endif
            </p>
        </div>

        {{-- Transaction count --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Số giao dịch</p>
            <p class="text-2xl font-bold text-gray-800">{{ $todaySummary->transaction_count }}</p>
            <p class="text-xs text-gray-400 mt-1">hôm nay</p>
        </div>

        {{-- Avg per transaction --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Trung bình / GD</p>
            @php
                $avg = $todaySummary->transaction_count > 0
                    ? $todaySummary->total_amount / $todaySummary->transaction_count
                    : 0;
            @endphp
            <p class="text-2xl font-bold text-gray-800">{{ number_format($avg) }} <span class="text-sm font-normal text-gray-400">đ</span></p>
            <p class="text-xs text-gray-400 mt-1">hôm nay</p>
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        {{-- Source breakdown --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-700 text-sm mb-4">Theo nguồn tiền (hôm nay)</h2>

            @php
                $sources = [
                    'cash'    => ['label' => 'Tiền mặt',    'color' => 'bg-yellow-400', 'val' => $todaySummary->total_cash],
                    'bank_qr' => ['label' => 'QR Ngân hàng','color' => 'bg-blue-400',   'val' => $todaySummary->total_bank_qr],
                    'wallet'  => ['label' => 'Ví điện tử',  'color' => 'bg-purple-400', 'val' => $todaySummary->total_wallet],
                    'card'    => ['label' => 'Thẻ',         'color' => 'bg-green-400',  'val' => $todaySummary->total_card],
                ];
                $total = max((float) $todaySummary->total_amount, 1);
            @endphp

            <div class="space-y-3">
                @foreach($sources as $src)
                    @php $pctSrc = round($src['val'] / $total * 100); @endphp
                    <div>
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>{{ $src['label'] }}</span>
                            <span class="font-medium">{{ number_format($src['val']) }} đ</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="{{ $src['color'] }} h-1.5 rounded-full transition-all"
                                 style="width: {{ $pctSrc }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 30-day sparkline --}}
        <div class="md:col-span-2 bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-700 text-sm mb-4">Doanh thu 30 ngày qua</h2>

            @php
                $rangeValues  = array_values(array_map(fn($s) => (float) $s->total_amount, $range));
                $rangeLabels  = array_keys($range);
                $maxVal       = max(max($rangeValues), 1);
                $barCount     = count($rangeValues);
            @endphp

            <div class="flex items-end gap-0.5 h-24">
                @foreach($rangeValues as $i => $val)
                    @php
                        $h   = max(round($val / $maxVal * 100), $val > 0 ? 4 : 1);
                        $lbl = \Carbon\Carbon::parse($rangeLabels[$i])->format('d/m');
                        $isToday = $rangeLabels[$i] === $today;
                    @endphp
                    <div class="flex-1 flex flex-col items-center group relative">
                        <div class="w-full rounded-sm {{ $isToday ? 'bg-orange-400' : 'bg-orange-200 group-hover:bg-orange-300' }} transition"
                             style="height: {{ $h }}%">
                        </div>
                        {{-- Tooltip --}}
                        <div class="absolute bottom-full mb-1 hidden group-hover:block z-10 pointer-events-none">
                            <div class="bg-gray-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap">
                                {{ $lbl }}: {{ number_format($val) }} đ
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between text-xs text-gray-400 mt-1">
                <span>{{ \Carbon\Carbon::parse($rangeLabels[0])->format('d/m') }}</span>
                <span>{{ \Carbon\Carbon::parse(end($rangeLabels))->format('d/m') }}</span>
            </div>
        </div>

    </div>

    {{-- Recent Transactions --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700 text-sm">Giao dịch gần đây</h2>
            <a href="{{ route('transactions.index') }}" class="text-xs text-orange-500 hover:underline">
                Xem tất cả →
            </a>
        </div>

        @forelse($recentTransactions as $tx)
            @php
                $badge = match($tx->source) {
                    'cash'    => 'bg-yellow-100 text-yellow-700',
                    'bank_qr' => 'bg-blue-100 text-blue-700',
                    'wallet'  => 'bg-purple-100 text-purple-700',
                    'card'    => 'bg-green-100 text-green-700',
                    default   => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                        {{ \App\Models\Transaction::sourceLabel($tx->source) }}
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ number_format($tx->amount) }} đ</p>
                        <p class="text-xs text-gray-400">
                            {{ $tx->transacted_at->timezone($tz)->format('H:i d/m') }}
                            @if($tx->note) · {{ Str::limit($tx->note, 40) }} @endif
                        </p>
                    </div>
                </div>
                @if($tx->employee)
                    <span class="text-xs text-gray-400 hidden sm:inline">{{ $tx->employee->name }}</span>
                @endif
            </div>
        @empty
            <div class="py-10 text-center text-gray-400 text-sm">Chưa có giao dịch nào.</div>
        @endforelse
    </div>

@endsection

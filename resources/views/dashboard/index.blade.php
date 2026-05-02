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

    {{-- Row 1: KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

        @php
            $diff    = $todaySummary->total_amount - $yesterdaySummary->total_amount;
            $pct     = $yesterdaySummary->total_amount > 0
                         ? round($diff / $yesterdaySummary->total_amount * 100, 1)
                         : null;
            $diffPos = $diff >= 0;
        @endphp

        {{-- Hôm nay --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Doanh thu hôm nay</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($todaySummary->total_amount) }} <span class="text-sm font-normal text-gray-400">đ</span></p>
            <p class="text-xs mt-1 {{ $diffPos ? 'text-green-600' : 'text-red-500' }}">
                {{ $diffPos ? '▲' : '▼' }} {{ number_format(abs($diff)) }} đ so hôm qua
                @if($pct !== null)({{ $pct }}%)@endif
            </p>
        </div>

        {{-- Tuần này --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Tuần này</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($thisWeekTotal) }} <span class="text-sm font-normal text-gray-400">đ</span></p>
            <p class="text-xs text-gray-400 mt-1">T2 → hôm nay</p>
        </div>

        {{-- Tháng này --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Tháng này</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($thisMonthTotal) }} <span class="text-sm font-normal text-gray-400">đ</span></p>
            <p class="text-xs text-gray-400 mt-1">{{ number_format($thisMonthCount) }} giao dịch</p>
        </div>

        {{-- Tháng trước --}}
        @php
            $monthPct = $lastMonthTotal > 0
                ? round(($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal * 100, 1)
                : null;
            $monthPos = $thisMonthTotal >= $lastMonthTotal;
        @endphp
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Tháng trước</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($lastMonthTotal) }} <span class="text-sm font-normal text-gray-400">đ</span></p>
            @if($monthPct !== null)
                <p class="text-xs mt-1 {{ $monthPos ? 'text-green-600' : 'text-red-500' }}">
                    Tháng này {{ $monthPos ? '▲' : '▼' }} {{ abs($monthPct) }}%
                </p>
            @else
                <p class="text-xs text-gray-400 mt-1">{{ number_format($lastMonthCount) }} giao dịch</p>
            @endif
        </div>

    </div>

    {{-- Row 2: Nguồn tiền hôm nay + Biểu đồ 30 ngày --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        {{-- Source breakdown hôm nay --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-700 text-sm mb-4">Theo nguồn tiền (hôm nay)</h2>
            @php
                $sources = [
                    'cash'    => ['label' => 'Tiền mặt',    'color' => 'bg-yellow-400', 'val' => (float) $todaySummary->total_cash],
                    'bank_qr' => ['label' => 'QR Ngân hàng','color' => 'bg-blue-400',   'val' => (float) $todaySummary->total_bank_qr],
                    'wallet'  => ['label' => 'Ví điện tử',  'color' => 'bg-purple-400', 'val' => (float) $todaySummary->total_wallet],
                    'card'    => ['label' => 'Thẻ',         'color' => 'bg-green-400',  'val' => (float) $todaySummary->total_card],
                ];
                $totalToday = max((float) $todaySummary->total_amount, 1);
            @endphp
            <div class="space-y-3">
                @foreach($sources as $src)
                    @php $pctSrc = round($src['val'] / $totalToday * 100); @endphp
                    <div>
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>{{ $src['label'] }}</span>
                            <span class="font-medium">{{ number_format($src['val']) }} đ</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="{{ $src['color'] }} h-1.5 rounded-full" style="width: {{ $pctSrc }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 30-day sparkline --}}
        <div class="md:col-span-2 bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-700 text-sm mb-4">Doanh thu 30 ngày qua</h2>
            @php
                $rangeValues = array_values(array_map(fn($s) => (float) $s->total_amount, $range));
                $rangeLabels = array_keys($range);
                $maxVal      = max(max($rangeValues), 1);
            @endphp
            <div class="flex gap-0.5 h-24">
                @foreach($rangeValues as $i => $val)
                    @php
                        $h       = max(round($val / $maxVal * 100), $val > 0 ? 4 : 1);
                        $lbl     = \Carbon\Carbon::parse($rangeLabels[$i])->format('d/m');
                        $isToday = $rangeLabels[$i] === $today;
                    @endphp
                    <div class="flex-1 h-full flex flex-col justify-end group relative">
                        <div class="w-full rounded-sm {{ $isToday ? 'bg-orange-400' : 'bg-orange-200 group-hover:bg-orange-300' }} transition"
                             style="height: {{ $h }}%"></div>
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

    {{-- Row 3: Nguồn 30 ngày + Top 5 ngày + Ca & Import --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">

        {{-- Nguồn tiền 30 ngày --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-700 text-sm mb-4">Nguồn tiền 30 ngày qua</h2>
            @php
                $src30List = [
                    'cash'    => ['label' => 'Tiền mặt',    'color' => 'bg-yellow-400', 'val' => (float) $source30->cash],
                    'bank_qr' => ['label' => 'QR Ngân hàng','color' => 'bg-blue-400',   'val' => (float) $source30->bank_qr],
                    'wallet'  => ['label' => 'Ví điện tử',  'color' => 'bg-purple-400', 'val' => (float) $source30->wallet],
                    'card'    => ['label' => 'Thẻ',         'color' => 'bg-green-400',  'val' => (float) $source30->card],
                ];
                $total30 = max((float) $source30->total, 1);
            @endphp
            <div class="space-y-3">
                @foreach($src30List as $src)
                    @php $p = round($src['val'] / $total30 * 100); @endphp
                    <div>
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>{{ $src['label'] }}</span>
                            <span class="font-medium">{{ number_format($src['val']) }} đ</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="{{ $src['color'] }} h-1.5 rounded-full" style="width: {{ $p }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-4 pt-3 border-t border-gray-100">
                Tổng: <span class="font-medium text-gray-600">{{ number_format($source30->total) }} đ</span>
            </p>
        </div>

        {{-- Top 5 ngày trong tháng --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h2 class="font-semibold text-gray-700 text-sm mb-4">Top ngày doanh thu cao (tháng này)</h2>
            @if($top5Days->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">Chưa có dữ liệu.</p>
            @else
                @php $maxDay = (float) $top5Days->first()->total_amount; @endphp
                <div class="space-y-3">
                    @foreach($top5Days as $i => $day)
                        @php $barW = round((float) $day->total_amount / $maxDay * 100); @endphp
                        <div>
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span class="font-medium">
                                    {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : '#' . ($i + 1))) }}
                                    {{ $day->summary_date->format('d/m/Y') }}
                                </span>
                                <span class="font-medium">{{ number_format($day->total_amount) }} đ</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                <div class="bg-orange-400 h-1.5 rounded-full" style="width: {{ $barW }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Ca hôm nay + Import gần đây --}}
        <div class="space-y-4">

            {{-- Ca hôm nay --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h2 class="font-semibold text-gray-700 text-sm mb-3">Ca làm hôm nay</h2>
                @if($todayByShift->isEmpty())
                    <p class="text-xs text-gray-400">Chưa có giao dịch theo ca.</p>
                @else
                    <div class="space-y-2">
                        @foreach($todayByShift as $shiftId => $data)
                            <div class="flex justify-between text-xs">
                                <span class="text-gray-600">
                                    {{ $data['shift']?->name ?? 'Không có ca' }}
                                </span>
                                <span class="font-medium text-gray-800">
                                    {{ number_format($data['total']) }} đ
                                    <span class="text-gray-400 font-normal">({{ $data['count'] }} GD)</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Import gần đây --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-700 text-sm">Import gần đây</h2>
                    <a href="{{ route('import.index') }}" class="text-xs text-orange-500 hover:underline">Xem tất cả →</a>
                </div>
                @forelse($recentBatches as $batch)
                    @php
                        $bColor = match($batch->status) {
                            'done'       => 'bg-green-100 text-green-700',
                            'failed'     => 'bg-red-100 text-red-600',
                            'processing' => 'bg-blue-100 text-blue-600',
                            default      => 'bg-gray-100 text-gray-500',
                        };
                        $bLabel = match($batch->status) {
                            'done' => 'Xong', 'failed' => 'Lỗi',
                            'processing' => 'Đang xử lý', default => 'Chờ',
                        };
                    @endphp
                    <div class="flex items-center justify-between py-1.5 text-xs">
                        <span class="text-gray-600 truncate max-w-[120px]">{{ basename($batch->filename) }}</span>
                        <div class="flex items-center gap-1.5 shrink-0">
                            @if($batch->status === 'done')
                                <span class="text-gray-400">{{ number_format($batch->imported_count) }} GD</span>
                            @endif
                            <span class="px-1.5 py-0.5 rounded-full font-medium {{ $bColor }}">{{ $bLabel }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400">Chưa có import nào.</p>
                @endforelse
            </div>

        </div>

    </div>

    {{-- Row 4: Giao dịch gần đây --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-700 text-sm">Giao dịch gần đây</h2>
            <a href="{{ route('transactions.index') }}" class="text-xs text-orange-500 hover:underline">Xem tất cả →</a>
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

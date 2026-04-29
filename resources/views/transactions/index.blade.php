@extends('layouts.app')

@section('title', 'Lịch sử giao dịch')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-800">Lịch sử giao dịch</h1>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('transactions.index') }}"
          class="bg-white rounded-2xl border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Từ ngày</label>
                <input type="date" name="from" value="{{ request('from') }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Đến ngày</label>
                <input type="date" name="to" value="{{ request('to') }}"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nguồn tiền</label>
                <select name="source"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                               focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none">
                    <option value="">Tất cả</option>
                    @foreach($sources as $src)
                        <option value="{{ $src }}" @selected(request('source') === $src)>
                            {{ \App\Models\Transaction::sourceLabel($src) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tìm kiếm</label>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Ghi chú hoặc mã tham chiếu"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm
                              focus:border-orange-400 focus:ring-2 focus:ring-orange-100 outline-none">
            </div>

        </div>

        <div class="mt-3 flex gap-2">
            <button type="submit"
                    class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                Lọc
            </button>
            <a href="{{ route('transactions.index') }}"
               class="text-sm text-gray-500 hover:text-gray-800 px-4 py-2 rounded-lg border border-gray-200 transition">
                Xoá bộ lọc
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">

        @if($transactions->isEmpty())
            <div class="py-16 text-center text-gray-400">
                <p class="text-3xl mb-2">📋</p>
                <p class="text-sm">Không có giao dịch nào.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                            <th class="px-4 py-3 text-left">Thời gian</th>
                            <th class="px-4 py-3 text-left">Nguồn</th>
                            <th class="px-4 py-3 text-right">Số tiền</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Ghi chú</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Ca / Nhân viên</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Nguồn nhập</th>
                            @if(auth()->user()->isOwner())
                                <th class="px-4 py-3"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($transactions as $tx)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                    {{ $tx->transacted_at->timezone($tz)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $badge = match($tx->source) {
                                            'cash'    => 'bg-yellow-100 text-yellow-700',
                                            'bank_qr' => 'bg-blue-100 text-blue-700',
                                            'wallet'  => 'bg-purple-100 text-purple-700',
                                            'card'    => 'bg-green-100 text-green-700',
                                            default   => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                        {{ \App\Models\Transaction::sourceLabel($tx->source) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-800 whitespace-nowrap">
                                    {{ number_format($tx->amount) }} đ
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden md:table-cell max-w-xs truncate">
                                    {{ $tx->note ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 hidden lg:table-cell">
                                    @if($tx->shift)
                                        <span>{{ $tx->shift->name }}</span>
                                    @endif
                                    @if($tx->employee)
                                        <span class="text-gray-400"> / {{ $tx->employee->name }}</span>
                                    @endif
                                    @if(! $tx->shift && ! $tx->employee)
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-400 text-xs hidden lg:table-cell">
                                    @if($tx->importBatch)
                                        <span class="inline-block px-2 py-0.5 rounded bg-gray-100">Import</span>
                                    @elseif($tx->reference_id && str_starts_with($tx->reference_id, 'email-'))
                                        <span class="inline-block px-2 py-0.5 rounded bg-indigo-50 text-indigo-600">Email</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded bg-orange-50 text-orange-600">Thủ công</span>
                                    @endif
                                </td>
                                @if(auth()->user()->isOwner())
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST"
                                              action="{{ route('transactions.destroy', $tx) }}"
                                              onsubmit="return confirm('Xoá giao dịch này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs text-red-400 hover:text-red-600 transition">
                                                Xoá
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($transactions->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $transactions->links() }}
                </div>
            @endif

            <div class="px-4 py-2 border-t border-gray-100 text-xs text-gray-400">
                {{ number_format($transactions->total()) }} giao dịch · trang {{ $transactions->currentPage() }}/{{ $transactions->lastPage() }}
            </div>
        @endif
    </div>
@endsection

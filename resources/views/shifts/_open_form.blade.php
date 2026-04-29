{{-- Widget mở ca — nhúng vào employees/index và dashboard --}}
@php
    $openShifts = \App\Models\Shift::where('store_id', \App\Helpers\StoreContext::id())
        ->whereNull('ended_at')
        ->with('employee')
        ->orderBy('started_at')
        ->get();

    $activeEmployees = \App\Models\Employee::where('store_id', \App\Helpers\StoreContext::id())
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    $shiftPresets = ['Sáng', 'Chiều', 'Tối', 'Cả ngày'];
@endphp

<div class="bg-white rounded-2xl border border-gray-200 p-5">
    <h2 class="font-semibold text-gray-700 mb-4">Ca làm việc hôm nay</h2>

    {{-- Ca đang mở --}}
    @if($openShifts->isNotEmpty())
        <div class="space-y-2 mb-4">
            @foreach($openShifts as $shift)
                <div class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-4 py-3">
                    <div>
                        <span class="font-medium text-green-800">{{ $shift->name }}</span>
                        <span class="text-green-600 text-sm ml-2">— {{ $shift->employee?->name }}</span>
                        <span class="text-green-500 text-xs ml-2">
                            từ {{ $shift->started_at->timezone(session('active_store_timezone', 'Asia/Ho_Chi_Minh'))->format('H:i') }}
                        </span>
                    </div>
                    <form method="POST" action="{{ route('shifts.close', $shift) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="text-sm text-red-500 hover:text-red-600 font-medium transition">
                            Đóng ca
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Form mở ca mới --}}
    @if(auth()->user()->isOwner())
        <form method="POST" action="{{ route('shifts.open') }}" class="flex flex-wrap gap-3 items-end">
            @csrf

            <div>
                <label class="block text-xs text-gray-500 mb-1">Ca</label>
                <select name="name"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 bg-white">
                    @foreach($shiftPresets as $preset)
                        <option value="{{ $preset }}">{{ $preset }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-gray-500 mb-1">Nhân viên</label>
                <select name="employee_id"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm outline-none focus:border-orange-400 focus:ring-2 focus:ring-orange-100 bg-white">
                    @forelse($activeEmployees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                    @empty
                        <option disabled>Chưa có nhân viên</option>
                    @endforelse
                </select>
            </div>

            <button type="submit"
                class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg px-4 py-2 transition"
                @if($activeEmployees->isEmpty()) disabled @endif>
                Mở ca
            </button>
        </form>
    @endif
</div>

{{-- Partial dùng chung cho create & edit --}}

{{-- Tên cửa hàng --}}
<div>
    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
        Tên cửa hàng <span class="text-red-500">*</span>
    </label>
    <input
        id="name"
        type="text"
        name="name"
        value="{{ old('name', $store?->name) }}"
        required
        autofocus
        class="w-full rounded-lg border px-3 py-2.5 text-sm outline-none transition
               @error('name') border-red-400 bg-red-50 @else border-gray-300 @enderror
               focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
        placeholder="Quán Cà Phê Sáng"
    >
    @error('name')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

{{-- Địa chỉ --}}
<div>
    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
        Địa chỉ
        <span class="text-gray-400 font-normal">(không bắt buộc)</span>
    </label>
    <input
        id="address"
        type="text"
        name="address"
        value="{{ old('address', $store?->address) }}"
        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none transition
               focus:border-orange-400 focus:ring-2 focus:ring-orange-100"
        placeholder="123 Nguyễn Huệ, Q.1, TP.HCM"
    >
    @error('address')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

{{-- Múi giờ --}}
<div>
    <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">
        Múi giờ <span class="text-red-500">*</span>
    </label>
    <select
        id="timezone"
        name="timezone"
        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm outline-none transition
               focus:border-orange-400 focus:ring-2 focus:ring-orange-100 bg-white"
    >
        @php
            $selected = old('timezone', $store?->timezone ?? 'Asia/Ho_Chi_Minh');
            $timezones = [
                'Asia/Ho_Chi_Minh' => 'Việt Nam (UTC+7)',
                'Asia/Bangkok'     => 'Bangkok (UTC+7)',
                'Asia/Singapore'   => 'Singapore (UTC+8)',
            ];
        @endphp
        @foreach($timezones as $value => $label)
            <option value="{{ $value }}" @selected($selected === $value)>
                {{ $label }}
            </option>
        @endforeach
    </select>
    @error('timezone')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

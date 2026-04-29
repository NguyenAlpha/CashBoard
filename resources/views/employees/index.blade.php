@extends('layouts.app')

@section('title', 'Nhân viên')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Nhân viên</h1>
        <a href="{{ route('employees.create') }}"
            class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-lg px-4 py-2 transition">
            + Thêm nhân viên
        </a>
    </div>

    {{-- Mở ca mới --}}
    @include('shifts._open_form')

    {{-- Danh sách nhân viên --}}
    <div class="mt-6 space-y-3">
        @forelse($employees as $employee)
            <div class="bg-white rounded-2xl border border-gray-200 p-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-orange-100 text-orange-600 font-semibold text-sm flex items-center justify-center">
                        {{ mb_strtoupper(mb_substr($employee->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-800">{{ $employee->name }}</p>
                        <p class="text-xs text-gray-400">{{ $employee->user?->email }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 text-sm">
                    @if($employee->is_active)
                        <span class="text-xs bg-green-100 text-green-600 font-medium px-2 py-0.5 rounded-full">
                            Đang làm
                        </span>
                    @else
                        <span class="text-xs bg-gray-100 text-gray-500 font-medium px-2 py-0.5 rounded-full">
                            Nghỉ
                        </span>
                    @endif

                    <a href="{{ route('employees.edit', $employee) }}"
                        class="text-gray-500 hover:text-gray-700 transition">Sửa</a>

                    <form method="POST" action="{{ route('employees.toggle', $employee) }}">
                        @csrf
                        <button type="submit"
                            class="text-gray-400 hover:text-gray-600 transition">
                            {{ $employee->is_active ? 'Vô hiệu hoá' : 'Kích hoạt' }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center text-gray-400">
                <p class="text-3xl mb-2">👤</p>
                <p>Chưa có nhân viên nào.</p>
                <a href="{{ route('employees.create') }}" class="text-orange-500 text-sm hover:underline mt-1 inline-block">
                    Thêm ngay
                </a>
            </div>
        @endforelse
    </div>
@endsection

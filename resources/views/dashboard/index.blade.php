@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Xin chào, {{ auth()->user()->name }} 👋</h1>
        <p class="text-gray-500 text-sm mt-1">Dashboard đang được xây dựng — TASK-09</p>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center text-gray-400">
        <p class="text-4xl mb-3">📊</p>
        <p class="font-medium text-gray-600">Dashboard sẽ hiển thị doanh thu tại đây.</p>
        <p class="text-sm mt-1">Coming soon in TASK-09.</p>
    </div>
@endsection

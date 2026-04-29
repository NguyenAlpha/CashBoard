<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(): View
    {
        return view('stores.index');
    }

    public function create(): View
    {
        return view('stores.create');
    }

    public function store(): RedirectResponse
    {
        // TODO: TASK-03
        return redirect()->route('dashboard');
    }

    public function edit(): View
    {
        return view('stores.edit');
    }

    public function update(): RedirectResponse
    {
        // TODO: TASK-03
        return redirect()->route('dashboard');
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\StoreContext;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(): View
    {
        $stores = auth()->user()->stores()->orderBy('created_at')->get();

        return view('stores.index', compact('stores'));
    }

    public function create(): View
    {
        return view('stores.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'address'  => ['nullable', 'string', 'max:500'],
            'timezone' => ['required', 'timezone'],
        ]);

        $store = auth()->user()->stores()->create($data);

        StoreContext::activate($store);

        return redirect()->route('dashboard')
            ->with('success', "Cửa hàng \"{$store->name}\" đã được tạo!");
    }

    public function edit(Store $store): View
    {
        $this->authorizeStore($store);

        return view('stores.edit', compact('store'));
    }

    public function update(Request $request, Store $store): RedirectResponse
    {
        $this->authorizeStore($store);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'address'  => ['nullable', 'string', 'max:500'],
            'timezone' => ['required', 'timezone'],
        ]);

        $store->update($data);

        // Cập nhật tên store trong session nếu đang active
        if (StoreContext::id() === $store->id) {
            StoreContext::activate($store->fresh());
        }

        return redirect()->route('stores.index')
            ->with('success', "Đã cập nhật cửa hàng \"{$store->name}\".");
    }

    public function activate(Store $store): RedirectResponse
    {
        $this->authorizeStore($store);

        StoreContext::activate($store);

        return redirect()->back()
            ->with('success', "Đã chuyển sang cửa hàng \"{$store->name}\".");
    }

    private function authorizeStore(Store $store): void
    {
        if ($store->user_id !== auth()->id()) {
            abort(403);
        }
    }
}

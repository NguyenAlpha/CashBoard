<?php

namespace App\Helpers;

use App\Models\Store;

class StoreContext
{
    public static function id(): ?int
    {
        return session('active_store_id');
    }

    public static function current(): ?Store
    {
        $id = session('active_store_id');

        return $id ? Store::find($id) : null;
    }

    public static function activate(Store $store): void
    {
        session([
            'active_store_id'       => $store->id,
            'active_store_name'     => $store->name,
            'active_store_timezone' => $store->timezone,
        ]);
    }
}

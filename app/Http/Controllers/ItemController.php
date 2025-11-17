<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Gate;

class ItemController extends Controller
{
    public function printQr(Item $item)
    {
        Gate::authorize('view', $item);

        $url = url("/admin/team/{$item->team->slug}/items/{$item->id}");
        $qrCode = QrCode::size(300)->generate($url);

        return view('print-qr', compact('item', 'qrCode'));
    }
}
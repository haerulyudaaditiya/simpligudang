<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ItemController extends Controller
{
    public function printQr(Item $item)
    {
        // Pastikan user punya akses ke item ini (Multi-tenant security)
        // Kita gunakan Policy yang sudah kita buat!
        $this->authorize('view', $item);

        // Generate QR Code (hanya SVG string)
        // Kita arahkan QR ke URL Filament Admin
        $url = url("/admin/team/{$item->team->slug}/items/{$item->id}");
        $qrCode = QrCode::size(300)->generate($url);

        return view('print-qr', compact('item', 'qrCode'));
    }
}

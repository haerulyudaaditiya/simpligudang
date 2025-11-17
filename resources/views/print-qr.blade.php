<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print QR - {{ $item->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen bg-gray-100 p-10">

    <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-300 flex flex-col items-center max-w-sm text-center print:shadow-none print:border-none">
        <h1 class="text-xl font-bold mb-1">{{ $item->team->name }}</h1>
        <h2 class="text-lg text-gray-600 mb-4">{{ $item->name }}</h2>

        <div class="mb-4">
            {!! $qrCode !!}
        </div>

        <p class="font-mono text-sm bg-gray-200 px-2 py-1 rounded">{{ $item->item_code ?? 'NO CODE' }}</p>
        <p class="text-xs text-gray-400 mt-2">{{ $item->category->name }} • {{ $item->location->name }}</p>
    </div>

    <button onclick="window.print()" class="no-print mt-8 px-6 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
        🖨️ Cetak Label
    </button>

</body>
</html>

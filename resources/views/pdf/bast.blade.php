<!DOCTYPE html>
<html>
<head>
    <title>Berita Acara Serah Terima</title>
    <style>
        body { font-family: sans-serif; font-size: 12pt; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .title { font-weight: bold; font-size: 16pt; margin-bottom: 5px; }
        .subtitle { font-size: 10pt; }
        .content { margin-top: 20px; line-height: 1.6; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        .table td, .table th { border: 1px solid #333; padding: 8px; }
        .signatures { margin-top: 50px; width: 100%; }
        .sig-box { width: 45%; float: left; text-align: center; }
        .sig-box.right { float: right; }
        .line { margin-top: 60px; border-top: 1px solid #000; width: 80%; margin-left: auto; margin-right: auto; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ strtoupper($team->name) }}</div>
        <div class="subtitle">Dokumen Resmi Manajemen Aset</div>
    </div>

    <div style="text-align: center; margin-bottom: 20px;">
        <h3>BERITA ACARA SERAH TERIMA BARANG</h3>
        <p>Nomor: BAST/{{ date('Y/m', strtotime($request->borrow_date)) }}/{{ substr($request->id, 0, 8) }}</p>
    </div>

    <div class="content">
        <p>Pada hari ini, <strong>{{ date('d F Y', strtotime($request->borrow_date)) }}</strong>, kami yang bertanda tangan di bawah ini:</p>

        <table style="width: 100%; margin-bottom: 15px;">
            <tr>
                <td style="width: 30%">Nama Pihak Pertama</td>
                <td>: <strong>{{ $approver->name ?? 'Admin Sistem' }}</strong> (Mewakili Perusahaan)</td>
            </tr>
            <tr>
                <td>Nama Pihak Kedua</td>
                <td>: <strong>{{ $user->name }}</strong> (Peminjam)</td>
            </tr>
        </table>

        <p>Pihak Pertama menyerahkan barang inventaris kepada Pihak Kedua dengan rincian sebagai berikut:</p>

        <table class="table">
            <thead>
                <tr style="background-color: #eee;">
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th>Kode Barang</th>
                    <th>Kondisi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->category->name }}</td>
                    <td>{{ $item->item_code ?? '-' }}</td>
                    <td>Baik / Layak Pakai</td>
                </tr>
            </tbody>
        </table>

        <p>Pihak Kedua menyatakan telah menerima barang tersebut dalam kondisi baik dan berjanji akan merawat serta mengembalikannya pada tanggal <strong>{{ date('d F Y', strtotime($request->return_date)) }}</strong>.</p>

        @if($request->reason)
        <p><em>Keperluan: {{ $request->reason }}</em></p>
        @endif
    </div>

    <div class="signatures">
        <div class="sig-box">
            <p>Pihak Pertama (Yang Menyerahkan)</p>
            <div class="line"></div>
            <p>{{ $approver->name ?? 'Admin' }}</p>
        </div>
        <div class="sig-box right">
            <p>Pihak Kedua (Penerima)</p>
            <div class="line"></div>
            <p>{{ $user->name }}</p>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Antrean — {{ config('app.name') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; }
        h1 { font-size: 16px; margin: 0 0 2px; color: #0f766e; }
        .meta { color: #64748b; font-size: 11px; margin: 0 0 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        thead th { background: #0f766e; color: #ffffff; }
        td.num, th.num { text-align: right; }
        tfoot td, tfoot th { background: #f1f5f9; font-weight: bold; }
        .ringkas { margin: 0 0 14px; }
        .ringkas span { display: inline-block; margin-right: 24px; }
    </style>
</head>
<body>
    <h1>Laporan &amp; Statistik Antrean</h1>
    <p class="meta">
        {{ config('app.name') }} · Periode {{ $labelRentang }}
        ({{ $periode === 'bulan' ? 'Bulanan' : 'Harian' }})
        · Dihasilkan {{ now()->format('d/m/Y H:i') }}
    </p>

    <p class="ringkas">
        <span>Total pasien: <strong>{{ number_format($total['jumlah']) }}</strong></span>
        <span>Rata-rata tunggu: <strong>{{ $total['rata_tunggu'] }}</strong></span>
        <span>Rata-rata layanan: <strong>{{ $total['rata_layanan'] }}</strong></span>
    </p>

    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Poli</th>
                <th>Lokasi Ruang</th>
                <th class="num">Jumlah Pasien</th>
                <th class="num">Rata-rata Tunggu (menit)</th>
                <th class="num">Rata-rata Layanan (menit)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['kode_poli'] }}</td>
                    <td>{{ $row['nama_poli'] }}</td>
                    <td>{{ $row['lokasi_ruang'] }}</td>
                    <td class="num">{{ number_format($row['jumlah']) }}</td>
                    <td class="num">{{ $row['rata_tunggu_teks'] }}</td>
                    <td class="num">{{ $row['rata_layanan_teks'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Total / Seluruh Poli</th>
                <th class="num">{{ number_format($total['jumlah']) }}</th>
                <th class="num">{{ $total['rata_tunggu'] }}</th>
                <th class="num">{{ $total['rata_layanan'] }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>

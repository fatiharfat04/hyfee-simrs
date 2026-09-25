<meta charset="utf-8">

<h3>Laporan Antrean — {{ config('app.name') }}</h3>
<p>
    Periode: {{ $labelRentang }} ({{ $periode === 'bulan' ? 'Bulanan' : 'Harian' }})
    | Dihasilkan: {{ now()->format('d/m/Y H:i') }}
</p>

<table>
    <thead>
        <tr>
            <th>Kode</th>
            <th>Poli</th>
            <th>Lokasi Ruang</th>
            <th>Jumlah Pasien</th>
            <th>Rata-rata Tunggu (menit)</th>
            <th>Rata-rata Layanan (menit)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row['kode_poli'] }}</td>
                <td>{{ $row['nama_poli'] }}</td>
                <td>{{ $row['lokasi_ruang'] }}</td>
                <td>{{ $row['jumlah'] }}</td>
                <td>{{ $row['rata_tunggu'] ?? '-' }}</td>
                <td>{{ $row['rata_layanan'] ?? '-' }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="3">Total / Seluruh Poli</th>
            <th>{{ $total['jumlah'] }}</th>
            <th>{{ $total['rata_tunggu'] }}</th>
            <th>{{ $total['rata_layanan'] }}</th>
        </tr>
    </tfoot>
</table>

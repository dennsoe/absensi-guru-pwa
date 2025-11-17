<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Kehadiran Guru - {{ $bulan }}/{{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
        }

        .header h2 {
            margin: 5px 0;
        }

        .info {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .footer {
            margin-top: 50px;
            text-align: right;
        }

        .signature {
            margin-top: 80px;
        }

        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h2>{{ $sekolah['nama'] ?? 'SEKOLAH' }}</h2>
        <p>{{ $sekolah['alamat'] ?? 'Alamat Sekolah' }}</p>
        <p>Telp: {{ $sekolah['telepon'] ?? '-' }} | Email: {{ $sekolah['email'] ?? '-' }}</p>
    </div>

    <!-- Title -->
    <h3 class="center">LAPORAN KEHADIRAN GURU</h3>
    <div class="info">
        <p>Periode: <strong>{{ $bulan_nama }} {{ $tahun }}</strong></p>
        <p>Tanggal Cetak: <strong>{{ now()->format('d F Y H:i') }}</strong></p>
    </div>

    <!-- Summary -->
    <table>
        <tr>
            <th>Total Guru</th>
            <th>Total Jadwal</th>
            <th>Rata-rata Kehadiran</th>
            <th>Total Terlambat</th>
        </tr>
        <tr class="center">
            <td>{{ $summary['total_guru'] }}</td>
            <td>{{ $summary['total_jadwal'] }}</td>
            <td><strong>{{ number_format($summary['rata_kehadiran'], 1) }}%</strong></td>
            <td>{{ $summary['total_terlambat'] }}</td>
        </tr>
    </table>

    <!-- Detail Table -->
    <table>
        <thead>
            <tr>
                <th class="center">No</th>
                <th>NIP</th>
                <th>Nama Guru</th>
                <th class="center">Hadir</th>
                <th class="center">Terlambat</th>
                <th class="center">Izin</th>
                <th class="center">Alpha</th>
                <th class="center">Total</th>
                <th class="center">%</th>
                <th class="center">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($laporan as $index => $lap)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $lap['guru']->nip }}</td>
                    <td>{{ $lap['guru']->nama }}</td>
                    <td class="center">{{ $lap['hadir'] }}</td>
                    <td class="center">{{ $lap['terlambat'] }}</td>
                    <td class="center">{{ $lap['izin'] }}</td>
                    <td class="center">{{ $lap['alpha'] }}</td>
                    <td class="center">{{ $lap['total_hari'] }}</td>
                    <td class="center">{{ number_format($lap['persentase'], 1) }}%</td>
                    <td class="center">
                        @if ($lap['persentase'] >= 90)
                            <span class="badge badge-success">Sangat Baik</span>
                        @elseif($lap['persentase'] >= 75)
                            <span class="badge badge-warning">Baik</span>
                        @else
                            <span class="badge badge-danger">Perlu Perhatian</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Notes -->
    <div style="margin-top: 20px; font-size: 10px;">
        <p><strong>Keterangan:</strong></p>
        <ul>
            <li>Sangat Baik: Kehadiran ≥ 90%</li>
            <li>Baik: Kehadiran 75% - 89%</li>
            <li>Perlu Perhatian: Kehadiran < 75%</li>
        </ul>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>{{ $sekolah['kota'] ?? 'Kota' }}, {{ now()->format('d F Y') }}</p>
        <p><strong>Kepala Sekolah / Bagian Kurikulum</strong></p>
        <div class="signature">
            <p><strong><u>{{ $penandatangan['nama'] ?? '___________________' }}</u></strong></p>
            <p>NIP. {{ $penandatangan['nip'] ?? '___________________' }}</p>
        </div>
    </div>
</body>

</html>

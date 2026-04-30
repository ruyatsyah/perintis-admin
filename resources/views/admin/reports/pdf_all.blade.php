<!DOCTYPE html>
<html>
<head>
    <title>Rekap Absensi Semua Kelas</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        /* Kop Surat */
        .header {
            border-bottom: 3px double #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
            position: relative;
        }
        .logo {
            position: absolute;
            top: 0;
            left: 0;
            width: 70px;
        }
        .header-text {
            text-align: center;
            margin-left: 80px;
            margin-right: 80px;
        }
        .header-text h1 {
            font-size: 18px;
            margin: 0;
            text-transform: uppercase;
        }
        .header-text h2 {
            font-size: 14px;
            margin: 2px 0;
            text-transform: uppercase;
        }
        .header-text p {
            font-size: 10px;
            margin: 2px 0;
            font-style: italic;
        }

        .title {
            text-align: center;
            text-transform: uppercase;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            text-decoration: underline;
        }

        table.main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            page-break-inside: auto;
        }
        table.main-table th, table.main-table td {
            border: 1px solid #000;
            padding: 5px;
        }
        table.main-table th {
            background-color: #f2f2f2;
            text-transform: uppercase;
            font-size: 10px;
        }
        .class-header {
            background-color: #e5e7eb;
            font-weight: bold;
            padding: 8px;
            border: 1px solid #000;
            margin-top: 20px;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    @foreach($allData as $data)
        @php
            $rec = $data['kelas'];
            $siswaData = $data['siswa'];
        @endphp
        <div class="header">
            @if(file_exists(public_path('assets/logo_perintis.png')))
                <img src="{{ public_path('assets/logo_perintis.png') }}" class="logo">
            @endif
            <div class="header-text">
                <h2>YAYASAN PENDIDIKAN PERINTIS</h2>
                <h1>SMK PERINTIS</h1>
                <p>Jl. Raya Perintis No. 123, Kota, Provinsi. Telp: (0123) 456789</p>
                <p>Email: info@smkperintis.sch.id | Website: www.smkperintis.sch.id</p>
            </div>
        </div>

        <div class="title">REKAPITULASI ABSENSI SISWA</div>
        <div class="class-header">
            KELAS: {{ $rec->nama_kelas }} ({{ $rec->jurusan->nama ?? '-' }})
            <br>
            Periode: {{ date('d/m/Y', strtotime($filters['start_date'])) }} - {{ date('d/m/Y', strtotime($filters['end_date'])) }}
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">NIS</th>
                    <th>Nama Siswa</th>
                    <th width="8%">H</th>
                    <th width="8%">S</th>
                    <th width="8%">I</th>
                    <th width="8%">A</th>
                    <th width="12%">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($siswaData as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $row->nis }}</td>
                        <td>{{ $row->nama }}</td>
                        <td class="text-center">{{ $row->total_hadir }}</td>
                        <td class="text-center">{{ $row->total_sakit }}</td>
                        <td class="text-center">{{ $row->total_izin }}</td>
                        <td class="text-center">{{ $row->total_alfa }}</td>
                        <td class="text-center font-bold">{{ $row->total_hadir + $row->total_sakit + $row->total_izin + $row->total_alfa }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>

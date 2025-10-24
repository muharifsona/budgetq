<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pengeluaran</title>
    <style>
        *{ font-family: DejaVu Sans, Arial, sans-serif; }
        table{ width:100%; border-collapse: collapse; }
        th,td{ border:1px solid #ddd; padding:6px; font-size:12px; }
        th{ background:#f5f5f5; text-align:left; }
        .right{ text-align:right; }
        .mb-8{ margin-bottom:16px; }
    </style>
</head>
<body>
    <h2 class="mb-8">Laporan Pengeluaran {{ str_pad($month,2,'0',STR_PAD_LEFT) }}/{{ $year }}</h2>

    <table class="mb-8">
        <tr>
            <th>Total Pengeluaran</th>
            <td class="right">{{ number_format($total,0,',','.') }}</td>
        </tr>
        @if($budget)
        <tr>
            <th>Budget</th>
            <td class="right">{{ number_format($budget->total_amount,0,',','.') }}</td>
        </tr>
        @endif
    </table>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Kategori</th>
                <th>Catatan</th>
                <th class="right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $e)
            <tr>
                <td>{{ \Illuminate\Support\Carbon::parse($e->date)->format('Y-m-d') }}</td>
                <td>{{ $e->category->name ?? '-' }}</td>
                <td>{{ $e->note }}</td>
                <td class="right">{{ number_format($e->amount,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

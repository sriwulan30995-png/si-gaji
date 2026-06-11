<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $payroll->employee->full_name }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .company-name {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .periode {
            font-size: 14px;
            margin-top: 5px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 4px;
            vertical-align: top;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .data-table th {
            background-color: #f4f4f4;
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-bold {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .signature-table {
            width: 100%;
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="company-name">PT. TRGATRA ANDIKARA DIGITAMA</div>
        <div class="periode">Slip Gaji Karyawan - Periode {{ str_pad($payroll->period_month, 2, '0', STR_PAD_LEFT) }} /
            {{ $payroll->period_year }}
        </div>
    </div>

    <!-- Informasi Pegawai -->
    <table class="info-table">
        <tr>
            <td width="15%" class="text-bold">Nama</td>
            <td width="35%">: {{ $payroll->employee->full_name }}</td>
            <td width="15%" class="text-bold">Posisi</td>
            <td width="35%">: {{ $payroll->employee->position->position_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="text-bold">NIK</td>
            <td>: {{ $payroll->employee->nik }}</td>
            <td class="text-bold">Status PTKP</td>
            <td>: {{ $payroll->employee->status_ptkp }}</td>
        </tr>
    </table>

    <!-- LOGIKA PERBAIKAN: Hitung ulang secara manual di Blade -->
    <!-- LOGIKA PERBAIKAN: Hitung mundur dari Net Salary (THP) yang sudah pasti benar -->
    @php
        // Sub Total Penghasilan = Gaji Bersih + Semua Potongan
        $subTotalPenghasilan = $payroll->net_salary + $payroll->total_deduction;
    @endphp

    <table class="data-table">
        <thead>
            <tr>
                <th>Deskripsi Komponen</th>
                <th>Penghasilan (Rp)</th>
                <th>Potongan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <!-- HAPUS BARIS GAJI POKOK MANUAL DI SINI KARENA SUDAH ADA DI DALAM DETAILS -->

            <!-- Looping Semua Detail Payroll (Termasuk Gaji Pokok dari Database) -->
            @foreach($payroll->details as $detail)
                <tr>
                    <td>{{ strtoupper($detail->component_name) }}</td>

                    <!-- Karena di database Gaji Pokok mungkin masuk sebagai 'allowance' atau tipe lain -->
                    @if(in_array($detail->type, ['allowance', 'overtime', 'basic_salary']))
                        <td class="text-right">{{ number_format($detail->amount, 0, ',', '.') }}</td>
                        <td class="text-right">-</td>
                    @else
                        <td class="text-right">-</td>
                        <td class="text-right">{{ number_format($detail->amount, 0, ',', '.') }}</td>
                    @endif
                </tr>
            @endforeach

            <!-- Sub Total -->
            <tr>
                <td class="text-bold text-center">Sub Total</td>
                <td class="text-right text-bold">
                    {{ number_format($subTotalPenghasilan, 0, ',', '.') }}
                </td>
                <td class="text-right text-bold">
                    {{ number_format($payroll->total_deduction, 0, ',', '.') }}
                </td>
            </tr>

            <!-- Take Home Pay (Net Salary) -->
            <tr>
                <td colspan="2" class="text-bold text-right" style="font-size: 14px; background-color: #f4f4f4;">
                    Total Gaji Bersih (Take Home Pay)
                </td>
                <td class="text-right text-bold" style="font-size: 14px; background-color: #f4f4f4;">
                    {{ number_format($payroll->net_salary, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Kolom Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td width="50%" class="text-center">
                Diterima Oleh,<br><br><br><br><br>
                <span class="text-bold">( {{ $payroll->employee->full_name }} )</span>
            </td>
            <td width="50%" class="text-center">
                Disetujui Oleh,<br><br><br><br><br>
                <span class="text-bold">( {{ $payroll->approver->name ?? 'HRD & Finance' }} )</span>
            </td>
        </tr>
    </table>

</body>

</html>
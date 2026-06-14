<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
// use Illuminate\Http\Request;

class PayslipController extends Controller
{
    public function download(Payroll $payroll)
    {
        // Pastikan hanya payroll yang sudah disetujui yang bisa dicetak
        if ($payroll->status !== 'approved') {
            abort(403, 'Slip gaji belum disetujui.');
        }

        // Load relasi yang diperlukan
        $payroll->load(['employee.position', 'details', 'overtimes']);
        // Generate PDF
        $pdf = Pdf::loadView('pdf.payslip', [
            'payroll' => $payroll,
        ]);

        $fileName = 'Slip_Gaji_' . $payroll->employee->nik . '_' . $payroll->period_month . '_' . $payroll->period_year . '.pdf';

        return $pdf->stream($fileName);
    }
}

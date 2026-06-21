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

        // Load relasi yang diperlukan
        $payroll->load(['employee.position', 'details', 'overtimes']);

        // Generate QR Code SVG Base64 di Controller
        $employeeQrText = "Dokumen ini secara resmi ditandatangani secara digital oleh " . $payroll->employee->full_name;
        $employeeQrBase64 = $this->generateQrCodeBase64($employeeQrText);

        $approverName = $payroll->approver->name ?? 'HRD & Finance';
        $approverQrText = "Dokumen ini secara resmi ditandatangani secara digital oleh " . $approverName;
        $approverQrBase64 = $this->generateQrCodeBase64($approverQrText);

        // Generate PDF
        $pdf = Pdf::loadView('pdf.payslip', [
            'payroll' => $payroll,
            'employeeQrBase64' => $employeeQrBase64,
            'approverQrBase64' => $approverQrBase64,
        ]);

        $fileName = 'Slip_Gaji_' . $payroll->employee->nik . '_' . $payroll->period_month . '_' . $payroll->period_year . '.pdf';

        return $pdf->stream($fileName);
    }

    private function generateQrCodeBase64($text)
    {
        // Menggunakan library simple-qrcode untuk men-generate format SVG murni (tanpa HTTP request)
        $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
            ->size(80)
            ->margin(0)
            ->generate($text);

        // Encode SVG menjadi base64 agar aman saat diparsing DOMPDF
        return base64_encode($svg);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'period_month',
        'period_year',
        'total_base_salary',
        'total_allowance',
        'total_overtime',
        'total_deduction',
        'net_salary',
        'status',
        'approved_by_user_id'
    ];

    public function details()
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function overtimes()
    {
        $startDate = Carbon::create($this->period_year, $this->period_month, 16)->startOfDay();

        // Periode berakhir di tanggal 15 bulan berikutnya
        // Kita gunakan addMonth() untuk melompat ke bulan depan
        $endDate = Carbon::create($this->period_year, $this->period_month, 15)
            ->addMonth()
            ->endOfDay();

        return $this->hasMany(Overtime::class, 'employee_id', 'employee_id')
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate, $endDate]);
    }
}


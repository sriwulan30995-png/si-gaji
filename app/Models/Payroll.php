<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}


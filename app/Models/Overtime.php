<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'duration_hours',
        'overtime_pay',
        'status',
        'approved_by_user_id'
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function getMultiplier15Attribute()
    {
        // Sesuaikan logika perhitungannya dengan aturan perusahaan Anda
        return $this->attributes['multiplier_1_5'] ?? 0;
    }

    public function getMultiplier2Attribute()
    {
        // Sesuaikan logika perhitungannya dengan aturan perusahaan Anda
        return $this->attributes['multiplier_2'] ?? 0;
    }
}

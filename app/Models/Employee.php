<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nik',
        'full_name',
        'status_ptkp',
        'position_id',
        'is_active',
        'joining_date'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'joining_date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function overtimes()
    {
        return $this->hasMany(Overtime::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = ['position_name', 'base_salary', 'hourly_overtime_rate'];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function allowances()
    {
        return $this->hasMany(PositionAllowance::class);
    }

    public function deductions()
    {
        return $this->hasMany(PositionDeduction::class);
    }
}


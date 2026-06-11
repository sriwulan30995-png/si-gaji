<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllowanceType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_daily'];
    protected $casts = ['is_daily' => 'boolean'];

    public function allowances()
    {
        return $this->hasMany(PositionAllowance::class);
    }
}


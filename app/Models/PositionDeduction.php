<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositionDeduction extends Model
{
    use HasFactory;

    protected $fillable = ['position_id', 'deduction_type_id', 'amount'];

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
    public function deductionType()
    {
        return $this->belongsTo(DeductionType::class, 'deduction_type_id');
    }
}


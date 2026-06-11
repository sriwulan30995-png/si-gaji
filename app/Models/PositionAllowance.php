<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PositionAllowance extends Model
{
    use HasFactory;

    protected $fillable = ['position_id', 'allowance_type_id', 'amount'];

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }
    public function allowanceType()
    {
        return $this->belongsTo(AllowanceType::class, 'allowance_type_id');
    }
}


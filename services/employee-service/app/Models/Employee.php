<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'position_id',
        'full_name',
        'email',
        'phone_number',
        'hire_date',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}

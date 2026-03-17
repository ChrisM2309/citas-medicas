<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'phone',
        'birth_date',
        'gender',
    ];

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enseignant extends Model
{
    use HasFactory;

    protected $fillable = [
        'telephone',
        'user_id'
    ];

    public function user(){
        $this->belongsTo(User::class);
    }
}

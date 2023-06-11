<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    protected $table = 'password_resets';
    protected $fillable = [
        'email',
        'token',
        'created_at'
    ];

    public $timestamps = false;

    public function scopeEmail($query , $email)
    {
        return $query->where('email' , $email);
    }

    public function scopeToken($query , $token)
    {
        return $query->where('token' , $token);
    }
}

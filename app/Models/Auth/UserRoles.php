<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRoles extends Model
{
    use HasFactory;

    protected $table = 'user_role';

    protected $fillable = [
        'name',
    ];

    public function permissions()
    {
        return $this->hasMany(UserAuth::class, 'role_id');
    }
}

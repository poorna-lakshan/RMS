<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;
    protected $table = 'user_activity';

    protected $fillable = [
        'name',
    ];

    public function permissions()
    {
        return $this->hasMany(UserAuth::class, 'activity_id');
    }
}

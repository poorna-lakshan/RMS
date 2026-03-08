<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAuth extends Model
{
    use HasFactory;

    protected $table = 'user_auth';

    protected $fillable = [
        'role_id',
        'activity_id', 
        'view',
        'add',
        'edit',
        'delete',
    ];


  protected $casts = [
        'role_id' => 'integer',
        'activity_id' => 'integer',
        'view' => 'boolean',
        'add' => 'boolean',
        'edit' => 'boolean',
         'delete' => 'boolean',
    ];
    public function UserRoles()
    {
        return $this->belongsTo(UserRoles::class, 'role_id');
    }

    public function UserActivity()
    {
        return $this->belongsTo(UserActivity::class, 'activity_id');
    }
}

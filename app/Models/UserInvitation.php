<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvitation extends Model
{
    protected $fillable = ['email', 'token', 'status', 'created_by_user_id'];
}

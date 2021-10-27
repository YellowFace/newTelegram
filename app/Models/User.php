<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    const ADMIN = 'admin';
    const MODERATOR = 'moderator';
    const USER = 'member';

    protected $table = 'user';

    protected $guarded = [];

    public $timestamps = false;
}

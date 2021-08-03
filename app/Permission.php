<?php

namespace App;

use Laratrust\Models\LaratrustPermission;

class Permission extends LaratrustPermission
{
    protected $table = 'permissions';
    protected $fillable = [
        'name',
        'display_name',
        'description'
    ];
}

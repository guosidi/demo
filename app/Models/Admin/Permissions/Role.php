<?php

namespace App\Models\Admin\Permissions;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = "admin_permissions_role";
    public $timestamps = false;

    private static $instance;

    public static function instance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }
}

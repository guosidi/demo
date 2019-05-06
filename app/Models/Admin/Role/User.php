<?php

namespace App\Models\Admin\Role;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = "admin_role_user";
    public $timestamps = false;

    private static $instance;

    public static function instance()
    {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }
}
